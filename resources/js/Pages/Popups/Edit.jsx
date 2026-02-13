import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState } from "react";

export default function CreateFAQ({ user,popup }) {
    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            id: popup.data.id,
            start_date: popup.data.start_date,
            end_date: popup.data.end_date,
            is_active: popup.data.is_active,
            image: popup.data.image,
        });
    const [imagePreview, setImagePreview] = useState(popup.data.image ?? null);

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        console.log("Hello");

        if (!file) return;

        setData("image", file);


        const reader = new FileReader();
        reader.onload = () => setImagePreview(reader.result);
        reader.readAsDataURL(file);
    };

    const removeImage = () => {
        setData("image", null);
        setImagePreview(null);
        const fileInput = document.querySelector("input[type=file]");
        fileInput.value = "";
    };

    const submit = (e) => {
        e.preventDefault();

        // 🔴 Frontend validation
        let hasError = false;

        clearErrors();

        if(!data.image) {
            setError("image", "Image is required");
            hasError = true;
        }

        if (!data.start_date) {
            setError("start_date", "Start Date is required");
            hasError = true;
        }

        if (!data.end_date) {
            setError("end_date", "End Date is required");
            hasError = true;
        }

        if (hasError) return;

        post("/popups/update");
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">
                        Create Popup
                    </h4>
                </div>

                {/* Back */}
                <button
                    className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15"
                    onClick={() => router.get("/popups")}
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

                {/* Form Card */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        {/* Question */}

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
                                    style={{ height: "444px" }}
                                >
                                    {imagePreview ? (
                                        <>
                                            <img
                                                src={imagePreview}
                                                alt="Preview"
                                                className="w-full h-full object-cover"
                                            />
                                            {/* Remove Button */}
                                            <button
                                                type="button"
                                                onClick={(e) => {
                                                    removeImage();
                                                }}
                                                className="absolute top-2 right-2 bg-black/50 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-500 transition"
                                            >
                                                ✕
                                            </button>
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
                                                Click to upload popup
                                                image
                                            </p>
                                            <p className="text-xs opacity-60 mt-1">
                                                JPG, PNG, WEBP
                                            </p>
                                        </div>
                                    )}
                                </div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={handleImageChange}
                                    className="hidden"
                                />
                                {errors.image && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.image}
                                </p>
                            )}
                            </label>
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Start Date <span className="text-red-400">*</span>
                            </label>

                            <input
                                type="date"
                                value={data.start_date}
                                onChange={(e) =>
                                    setData("start_date", e.target.value)
                                }
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

                        {/* Answer */}
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                End Date <span className="text-red-400">*</span>
                            </label>

                            <input
                                type="date"
                                value={data.end_date}
                                onChange={(e) =>
                                    setData("end_date", e.target.value)
                                }
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

                        {/* Active Toggle */}
                        <div className="flex items-center gap-3">
                            <label className="relative inline-block w-14 h-7 cursor-pointer">
                                <input
                                    type="checkbox"
                                    className="peer sr-only"
                                    checked={data.is_active}
                                    onChange={(e) =>
                                        setData("is_active", e.target.checked)
                                    }
                                />

                                <span className="absolute inset-0 bg-gray-300 rounded-lg transition peer-checked:bg-green-500"></span>
                                <span className="absolute left-1 bottom-1 w-5 h-5 bg-white rounded-md transition peer-checked:translate-x-6"></span>
                            </label>
                        </div>

                        {/* Submit */}
                        <div className="flex justify-end gap-3">
                            <Link href={route("popups")}>
                                <button
                                    type="button"
                                    className="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white"
                                >
                                    Cancel
                                </button>
                            </Link>

                            <button
                                type="submit"
                                // disabled={processing}
                                className="px-5 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white disabled:opacity-50"
                            >
                                Update
                            </button>
                        </div>
                    </form>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
