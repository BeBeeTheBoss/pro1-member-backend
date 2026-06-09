import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState } from "react";

export default function CreateEvent({ user, platforms }) {
    const [preview, setPreview] = useState(null);
    const [platformRows, setPlatformRows] = useState([
        { event_platform_id: "", link: "" },
    ]);

    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            name: "",
            description: "",
            image: null,
            start_date: "",
            start_time: "",
            end_date: "",
            end_time: "",
            platforms: [{ event_platform_id: "", link: "" }],
        });

    const syncPlatforms = (rows) => {
        setPlatformRows(rows);
        setData("platforms", rows);
    };

    const addRow = () => {
        const rows = [...platformRows, { event_platform_id: "", link: "" }];
        syncPlatforms(rows);
    };

    const removeRow = (idx) => {
        const rows = platformRows.filter((_, i) => i !== idx);
        syncPlatforms(rows.length ? rows : [{ event_platform_id: "", link: "" }]);
    };

    const updateRow = (idx, key, value) => {
        const rows = platformRows.map((row, i) =>
            i === idx ? { ...row, [key]: value } : row
        );
        syncPlatforms(rows);
    };

    const submit = (e) => {
        e.preventDefault();

        let hasError = false;
        clearErrors();

        if (!data.name.trim()) {
            setError("name", "Name is required");
            hasError = true;
        }

        if (!data.description.trim()) {
            setError("description", "Description is required");
            hasError = true;
        }

        if (!data.start_date) {
            setError("start_date", "Start date is required");
            hasError = true;
        }

        if (!data.start_time) {
            setError("start_time", "Start time is required");
            hasError = true;
        }

        if (!data.end_date) {
            setError("end_date", "End date is required");
            hasError = true;
        }

        if (!data.end_time) {
            setError("end_time", "End time is required");
            hasError = true;
        }

        if (
            data.start_date &&
            data.start_time &&
            data.end_date &&
            data.end_time &&
            new Date(`${data.end_date}T${data.end_time}`) <
                new Date(`${data.start_date}T${data.start_time}`)
        ) {
            setError(
                "end_date",
                "End date and time must be after or equal to start date and time"
            );
            hasError = true;
        }

        if (!data.image) {
            setError("image", "Image is required");
            hasError = true;
        }

        const validPlatforms = data.platforms.filter(
            (p) => p.event_platform_id && p.link.trim()
        );

        if (validPlatforms.length === 0) {
            setError("platforms", "At least one platform and link is required");
            hasError = true;
        }

        if (hasError) return;

        post("/events", { forceFormData: true });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">Create Event</h4>
                </div>

                <button
                    className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15"
                    onClick={() => router.get("/events")}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5 text-white mb-1"
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

                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Name <span className="text-red-400">*</span>
                            </label>

                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData("name", e.target.value)}
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.name
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            />

                            {errors.name && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Description <span className="text-red-400">*</span>
                            </label>

                            <textarea
                                rows={4}
                                value={data.description}
                                onChange={(e) => setData("description", e.target.value)}
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.description
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            />

                            {errors.description && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.description}
                                </p>
                            )}
                        </div>

                        <div className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-[90px_1fr_1fr] gap-4 items-start">
                                <label className="text-lg pt-2 text-white">
                                    Start <span className="text-red-400">*</span>
                                </label>

                                <div>
                                    <input
                                        type="date"
                                        value={data.start_date}
                                        onChange={(e) => setData("start_date", e.target.value)}
                                        className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                            ${
                                                errors.start_date
                                                    ? "ring-red-500"
                                                    : "focus:ring-indigo-400"
                                            }`}
                                    />
                                    {errors.start_date && (
                                        <p className="text-red-400 text-xs mt-1">
                                            {errors.start_date}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <input
                                        type="time"
                                        value={data.start_time}
                                        onChange={(e) => setData("start_time", e.target.value)}
                                        className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                            ${
                                                errors.start_time
                                                    ? "ring-red-500"
                                                    : "focus:ring-indigo-400"
                                            }`}
                                    />
                                    {errors.start_time && (
                                        <p className="text-red-400 text-xs mt-1">
                                            {errors.start_time}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-[90px_1fr_1fr] gap-4 items-start">
                                <label className="text-lg pt-2 text-white">
                                    End <span className="text-red-400">*</span>
                                </label>

                                <div>
                                    <input
                                        type="date"
                                        value={data.end_date}
                                        onChange={(e) => setData("end_date", e.target.value)}
                                        className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                            ${
                                                errors.end_date
                                                    ? "ring-red-500"
                                                    : "focus:ring-indigo-400"
                                            }`}
                                    />
                                    {errors.end_date && (
                                        <p className="text-red-400 text-xs mt-1">
                                            {errors.end_date}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <input
                                        type="time"
                                        value={data.end_time}
                                        onChange={(e) => setData("end_time", e.target.value)}
                                        className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                            ${
                                                errors.end_time
                                                    ? "ring-red-500"
                                                    : "focus:ring-indigo-400"
                                            }`}
                                    />
                                    {errors.end_time && (
                                        <p className="text-red-400 text-xs mt-1">
                                            {errors.end_time}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-center">
                            <label className="relative group w-full max-w-md cursor-pointer">
                                <div
                                    className={`rounded-2xl border-2 border-dashed
                                    ${
                                        errors.image
                                            ? "border-red-400"
                                            : "border-white/20 hover:border-indigo-400"
                                    }
                                    bg-white/5 overflow-hidden flex items-center justify-center
                                    transition`}
                                    style={{ height: "250px" }}
                                >
                                    {preview ? (
                                        <>
                                            <img
                                                src={preview}
                                                alt="Preview"
                                                className="w-full h-full object-cover"
                                            />
                                            <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                <span className="text-white text-sm font-medium">
                                                    Change Image
                                                </span>
                                            </div>
                                        </>
                                    ) : (
                                        <div className="flex flex-col items-center text-center text-gray-300">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-10 w-10 mb-2 opacity-70"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={1.5}
                                                    d="M3 16l4-4a3 3 0 014 0l4 4m0 0l4-4a3 3 0 014 0l2 2M5 8h14"
                                                />
                                            </svg>
                                            <p className="text-sm">
                                                Click to upload event image
                                            </p>
                                            <p className="text-xs opacity-60 mt-1">
                                                JPG, PNG, WEBP
                                            </p>
                                        </div>
                                    )}
                                </div>
                                <input
                                    id="eventImage"
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => {
                                        const file = e.target.files[0];
                                        setData("image", file);
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onloadend = () =>
                                                setPreview(reader.result);
                                            reader.readAsDataURL(file);
                                        } else {
                                            setPreview(null);
                                        }
                                    }}
                                    className="hidden"
                                />
                            </label>
                        </div>
                        {errors.image && (
                            <p className="text-red-400 text-xs mt-2 text-center">
                                {errors.image}
                            </p>
                        )}

                        <div className="bg-white/5 rounded-xl p-4">
                            <div className="flex items-center justify-between mb-3">
                                <label className="block text-lg text-white">
                                    Platforms & Links <span className="text-red-400">*</span>
                                </label>
                                <button
                                    type="button"
                                    onClick={addRow}
                                    className="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs"
                                >
                                    + Add
                                </button>
                            </div>

                            {platformRows.map((row, idx) => (
                                <div
                                    key={idx}
                                    className="grid grid-cols-1 md:grid-cols-12 gap-3 mb-3"
                                >
                                    <div className="md:col-span-4">
                                        <select
                                            value={row.event_platform_id}
                                            onChange={(e) =>
                                                updateRow(idx, "event_platform_id", e.target.value)
                                            }
                                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        >
                                            <option value="" className="text-black">
                                                Select platform
                                            </option>
                                            {platforms?.map((p) => (
                                                <option key={p.id} value={p.id} className="text-black">
                                                    {p.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="md:col-span-6">
                                        <input
                                            type="text"
                                            placeholder="https://..."
                                            value={row.link}
                                            onChange={(e) =>
                                                updateRow(idx, "link", e.target.value)
                                            }
                                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        />
                                    </div>

                                    <div className="md:col-span-2 flex gap-2">
                                        <button
                                            type="button"
                                            onClick={() => removeRow(idx)}
                                            className="w-full px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            ))}

                            {errors.platforms && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.platforms}
                                </p>
                            )}
                        </div>

                        <div className="flex justify-end gap-3">
                            <Link href={route("events")}>
                                <button
                                    type="button"
                                    className="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white"
                                >
                                    Cancel
                                </button>
                            </Link>

                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white disabled:opacity-50"
                            >
                                Create
                            </button>
                        </div>
                    </form>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
