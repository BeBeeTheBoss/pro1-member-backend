import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState } from "react";

export default function EditPrivilege({ privilege, user, categories }) {
    const [preview, setPreview] = useState(
        privilege.image ? `/storage/privileges/${privilege.image}` : null
    );
    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            id: privilege.id,
            title: privilege.title ?? "",
            description: privilege.description ?? "",
            image: null,
            start_date: privilege.start_date ?? "",
            end_date: privilege.end_date ?? "",
            category_id: privilege.category_id ?? "",
            is_active: privilege.is_active,
        });

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        setData("image", file);

        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => setPreview(reader.result);
            reader.readAsDataURL(file);
        } else {
            setPreview(
                privilege.image ? `/storage/privileges/${privilege.image}` : null
            );
        }
    };

    const submit = (e) => {
        e.preventDefault();

        let hasError = false;
        clearErrors();

        if (!data.title.trim()) {
            setError("title", "Title is required");
            hasError = true;
        }

        if (!data.category_id) {
            setError("category_id", "Category is required");
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

        if (!data.end_date) {
            setError("end_date", "End date is required");
            hasError = true;
        }

        if (!data.image) {
            setError("image", "Image is required");
            hasError = true;
        }

        if (hasError) return;

        post("/privileges/update", { forceFormData: true });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">Edit Privilege</h4>
                </div>

                <button
                    className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15"
                    onClick={() => router.get("/privileges")}
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
                                Title <span className="text-red-400">*</span>
                            </label>

                            <input
                                type="text"
                                value={data.title}
                                onChange={(e) => setData("title", e.target.value)}
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.title
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            />

                            {errors.title && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.title}
                                </p>
                            )}
                        </div>

                        <div className="bg-white/5 rounded-xl p-4">
                            <label className="block text-lg mb-3 text-white">
                                Image
                            </label>

                            {preview ? (
                                <img
                                    src={preview}
                                    alt="Preview"
                                    className="w-full h-48 object-cover rounded-xl border border-white/10"
                                />
                            ) : (
                                <div className="w-full h-48 flex items-center justify-center rounded-xl bg-white/10 text-white border border-white/10">
                                    <span className="opacity-70">
                                        No Image Selected
                                    </span>
                                </div>
                            )}

                            <input
                                id="privilegeImage"
                                type="file"
                                accept="image/*"
                                onChange={handleImageChange}
                                className="hidden"
                            />

                            <button
                                type="button"
                                onClick={() =>
                                    document.getElementById("privilegeImage").click()
                                }
                                className="mt-4 flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all shadow-md active:scale-95"
                            >
                                Upload Image
                            </button>
                            {errors.image && (
                                <p className="text-red-400 text-xs mt-2">
                                    {errors.image}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Category <span className="text-red-400">*</span>
                            </label>

                            <select
                                value={data.category_id}
                                onChange={(e) => setData("category_id", e.target.value)}
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.category_id
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            >
                                <option value="" className="text-black">
                                    Select a category
                                </option>
                                {categories?.map((c) => (
                                    <option key={c.id} value={c.id} className="text-black">
                                        {c.name}
                                    </option>
                                ))}
                            </select>

                            {errors.category_id && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.category_id}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Description
                            </label>

                            <textarea
                                rows={4}
                                value={data.description}
                                onChange={(e) => setData("description", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.description && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.description}
                                </p>
                            )}
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-lg mb-2 text-white">Start Date</label>
                                <input
                                    type="date"
                                    value={data.start_date}
                                    onChange={(e) => setData("start_date", e.target.value)}
                                    className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                />
                                {errors.start_date && (
                                    <p className="text-red-400 text-xs mt-1">
                                        {errors.start_date}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label className="block text-lg mb-2 text-white">End Date</label>
                                <input
                                    type="date"
                                    value={data.end_date}
                                    onChange={(e) => setData("end_date", e.target.value)}
                                    className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                />
                                {errors.end_date && (
                                    <p className="text-red-400 text-xs mt-1">
                                        {errors.end_date}
                                    </p>
                                )}
                            </div>
                        </div>

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

                        <div className="flex justify-end gap-3">
                            <Link href={route("privileges")}>
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
                                Update
                            </button>
                        </div>
                    </form>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
