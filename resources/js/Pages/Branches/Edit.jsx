import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import { MapContainer, TileLayer, Marker, useMapEvents } from "react-leaflet";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { router, Link } from "@inertiajs/react";

// Fix leaflet default marker icon
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
    iconUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
    shadowUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
});

// Map picker component
function LocationPicker({ lat, lng, setLat, setLng }) {
    function LocationMarker() {
        useMapEvents({
            click(e) {
                setLat(e.latlng.lat.toFixed(6));
                setLng(e.latlng.lng.toFixed(6));
            },
        });

        return lat && lng ? <Marker position={[lat, lng]} /> : null;
    }

    return (
        <MapContainer
            center={[lat || 20.0, lng || 95.0]}
            zoom={14}
            scrollWheelZoom={false}
            className="w-full h-64 rounded-lg"
        >
            <TileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                attribution="&copy; OpenStreetMap contributors"
            />
            <LocationMarker />
        </MapContainer>
    );
}

export default function Edit({ branch,user }) {
    const [preview, setPreview] = useState(branch.data.image);
    const [form, setForm] = useState({
        id: branch.data.id,
        name: branch.data.name,
        address: branch.data.address,
        contact: branch.data.contact,
        opening_time: branch.data.opening_time,
        closing_time: branch.data.closing_time,
        latitude: branch.data.latitude,
        longitude: branch.data.longitude,
        region: branch.data.region,
        township: branch.data.township,
        image: branch.data.image,
    });

    const [errors, setErrors] = useState({});

    // Input change handler
    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ ...prev, [name]: value }));

        // Clear error for this field when user types
        setErrors((prev) => ({ ...prev, [name]: "" }));
    };

    // Image change handler
    const handleImageChange = (e) => {
        const file = e.target.files[0];
        setForm((prev) => ({ ...prev, image: file }));

        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => setPreview(reader.result);
            reader.readAsDataURL(file);
        }

        setErrors((prev) => ({ ...prev, image: "" }));
    };

    // Form submit with validation
    const handleSubmit = (e) => {
        e.preventDefault();

        const newErrors = {};

        if (!form.name.trim()) newErrors.name = "Name is required";
        if (!form.address.trim()) newErrors.address = "Address is required";
        if (!form.contact.trim()) newErrors.contact = "Contact is required";
        if (!form.opening_time)
            newErrors.opening_time = "Opening time is required";
        if (!form.closing_time)
            newErrors.closing_time = "Closing time is required";
        if (!form.latitude) newErrors.latitude = "Latitude is required";
        if (!form.longitude) newErrors.longitude = "Longitude is required";
        if (!form.region.trim()) newErrors.region = "Region is required";
        if (!form.township.trim()) newErrors.township = "Township is required";
        if (!form.image) newErrors.image = "Image is required";

        setErrors(newErrors);

        if (Object.keys(newErrors).length > 0) return;

        // Submit form if no errors
        const formData = new FormData();
        Object.keys(form).forEach((key) => {
            formData.append(key, form[key]);
        });

        router.post("/branches/update", formData);
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-7xl mx-auto p-6">
                <div className="flex items-center justify-between">
                    <Link
                        href={route("branches")}
                        style={{ textDecoration: "none" }}
                    >
                        <button className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-6 w-6 text-white mb-2"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M15 9l-7 7 7-7-7 7 7 7 7 7-7z"
                                />
                            </svg>
                            <span className="ml-2 text-white text-lg">Back</span>
                        </button>
                    </Link>
                    <h4 className="font-bold mb-6 text-white text-center">
                        Edit Branch
                    </h4>
                    <div></div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Image Upload */}
                    <div className="col-span-1 bg-white/10 rounded-2xl p-4 flex flex-col items-center justify-center">
                        <label className="mb-3 font-semibold text-white text-lg">
                            Branch Image
                        </label>

                        {preview ? (
                            <img
                                src={preview}
                                alt="Preview"
                                className="w-[420px] h-[230px] object-cover rounded-xl border border-white/20 shadow-lg"
                            />
                        ) : (
                            <div className="w-[350px] h-[230px] flex items-center justify-center rounded-xl bg-white/20 text-white border border-white/10">
                                <span className="opacity-70">
                                    No Image Selected
                                </span>
                            </div>
                        )}

                        <input
                            id="fileUpload"
                            type="file"
                            accept="image/*"
                            onChange={handleImageChange}
                            className="hidden"
                        />

                        <button
                            type="button"
                            onClick={() =>
                                document.getElementById("fileUpload").click()
                            }
                            className="mt-4 flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md active:scale-95"
                        >
                            Upload Image
                        </button>
                        {errors.image && (
                            <p className="text-[#ff2111ff] mt-1 text-sm">
                                {errors.image}
                            </p>
                        )}
                    </div>

                    {/* Form Inputs */}
                    <div className="col-span-2 bg-white/10 p-6 rounded-2xl space-y-4">
                        {/* Name */}
                        <div>
                            <label className="block text-white font-medium mb-1">
                                Name
                            </label>
                            <input
                                type="text"
                                name="name"
                                value={form.name}
                                onChange={handleChange}
                                className="w-full px-4 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.name && (
                                <p className="text-[#ff2111ff] mt-1 text-sm">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        {/* Address */}
                        <div>
                            <label className="block text-white font-medium mb-1">
                                Address
                            </label>
                            <textarea
                                name="address"
                                value={form.address}
                                onChange={handleChange}
                                rows={3}
                                className="w-full px-4 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.address && (
                                <p className="text-[#ff2111ff] mt-1 text-sm">
                                    {errors.address}
                                </p>
                            )}
                        </div>

                        {/* Contact */}
                        <div>
                            <label className="block text-white font-medium mb-1">
                                Contact
                            </label>
                            <input
                                type="text"
                                name="contact"
                                value={form.contact}
                                onChange={handleChange}
                                className="w-full px-4 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.contact && (
                                <p className="text-[#ff2111ff] mt-1 text-sm">
                                    {errors.contact}
                                </p>
                            )}
                        </div>

                        {/* Opening / Closing Times */}
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-white font-medium mb-1">
                                    Opening Time
                                </label>
                                <input
                                    type="time"
                                    name="opening_time"
                                    value={form.opening_time}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                />
                                {errors.opening_time && (
                                    <p className="text-[#ff2111ff] mt-1 text-sm">
                                        {errors.opening_time}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="block text-white font-medium mb-1">
                                    Closing Time
                                </label>
                                <input
                                    type="time"
                                    name="closing_time"
                                    value={form.closing_time}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                />
                                {errors.closing_time && (
                                    <p className="text-[#ff2111ff] mt-1 text-sm">
                                        {errors.closing_time}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Map */}
                        <div>
                            <label className="block text-white font-medium mb-1">
                                Pick Location on Map
                            </label>
                            <LocationPicker
                                lat={form.latitude}
                                lng={form.longitude}
                                setLat={(val) =>
                                    setForm((prev) => ({
                                        ...prev,
                                        latitude: val,
                                    }))
                                }
                                setLng={(val) =>
                                    setForm((prev) => ({
                                        ...prev,
                                        longitude: val,
                                    }))
                                }
                            />

                            <div className="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <label className="block text-white font-medium mb-1">
                                        Latitude
                                    </label>
                                    <input
                                        type="text"
                                        value={form.latitude}
                                        readOnly
                                        className="w-full px-4 py-2 rounded-lg bg-white/10 text-white"
                                    />
                                    {errors.latitude && (
                                        <p className="text-[#ff2111ff] mt-1 text-sm">
                                            {errors.latitude}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-white font-medium mb-1">
                                        Longitude
                                    </label>
                                    <input
                                        type="text"
                                        value={form.longitude}
                                        readOnly
                                        className="w-full px-4 py-2 rounded-lg bg-white/10 text-white"
                                    />
                                    {errors.longitude && (
                                        <p className="text-[#ff2111ff] mt-1 text-sm">
                                            {errors.longitude}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Region / Township */}
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-white font-medium mb-1">
                                    Region
                                </label>
                                <input
                                    type="text"
                                    name="region"
                                    value={form.region}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 rounded-lg bg-white/10 text-white"
                                />
                                {errors.region && (
                                    <p className="text-[#ff2111ff] mt-1 text-sm">
                                        {errors.region}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="block text-white font-medium mb-1">
                                    Township
                                </label>
                                <input
                                    type="text"
                                    name="township"
                                    value={form.township}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2 rounded-lg bg-white/10 text-white"
                                />
                                {errors.township && (
                                    <p className="text-[#ff2111ff] mt-1 text-sm">
                                        {errors.township}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Submit */}
                        <div className="flex justify-end mt-4">
                            <button
                                onClick={handleSubmit}
                                className="px-6 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 shadow"
                            >
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
