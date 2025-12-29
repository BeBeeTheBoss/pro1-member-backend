import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState, useEffect } from "react";
import axios from "axios";

export default function UpdateNotification({ user, notification }) {
    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            id: notification.id,
            title: notification.title,
            message: notification.message,
            choice: notification.recipient, // all or specific
            user_id: notification.user?.id, // for single choice
            image: notification.image, // optional new image
        });

    const [imagePreview, setImagePreview] = useState(
        notification.image ?? null
    );
    const [searchTerm, setSearchTerm] = useState(
        notification.user
            ? `${notification.user?.name} (${notification.user?.phone})`
            : ""
    );
    const [searchResults, setSearchResults] = useState([]);

    /* ------------------- SEARCH USERS ------------------- */
    const searchUsers = async (query) => {
        if (!query) return setSearchResults([]);
        try {
            const response = await axios.get("/api/users/search", {
                params: { searchKey: query },
            });
            setSearchResults(response.data);
        } catch (error) {
            console.error(error);
        }
    };

    useEffect(() => {
        const timeout = setTimeout(() => searchUsers(searchTerm), 300);
        return () => clearTimeout(timeout);
    }, [searchTerm]);

    /* ------------------- IMAGE HANDLER ------------------- */
    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        setData("image", file);

        const reader = new FileReader();
        reader.onload = () => setImagePreview(reader.result);
        reader.readAsDataURL(file);
    };

    const removeImage = () => {
        setData("image", null);
        setImagePreview(null);
    };

    /* ------------------- SUBMIT ------------------- */
    const submit = (e) => {
        e.preventDefault();
        clearErrors();
        let hasError = false;

        if (!data.title.trim()) {
            setError("title", "Title is required");
            hasError = true;
        }
        if (!data.message.trim()) {
            setError("message", "Message is required");
            hasError = true;
        }
        if (data.choice === "specific" && !data.user_id) {
            setError("user_id", "Please select a user");
            hasError = true;
        }
        if (hasError) return;

        post("/notifications/update", { forceFormData: true });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">
                        Update Notification
                    </h4>
                </div>

                <button
                    className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15"
                    onClick={() => router.get("/notifications")}
                >
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

                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        {/* ------------------- IMAGE UPLOAD ------------------- */}
                        <div className="flex justify-center">
                            <label className="relative group w-full max-w-md cursor-pointer">
                                <div
                                    className={`rounded-2xl border-2 border-dashed
                                    ${
                                        errors.image
                                            ? "border-red-500"
                                            : "border-white/20 hover:border-indigo-400"
                                    }
                                    bg-white/5 overflow-hidden flex items-center justify-center transition`}
                                    style={{ height: "250px" }}
                                >
                                    {imagePreview ? (
                                        <>
                                            <img
                                                src={imagePreview}
                                                alt="Preview"
                                                className="w-full h-full object-cover"
                                            />
                                            <button
                                                type="button"
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    removeImage();
                                                }}
                                                className="absolute top-2 right-2 bg-black/50 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-500 transition"
                                            >
                                                ✕
                                            </button>
                                        </>
                                    ) : (
                                        <div className="flex flex-col items-center text-center text-gray-300">
                                            <p className="text-sm">
                                                Click to upload notification
                                                image
                                            </p>
                                            <p className="text-xs opacity-60 mt-1">
                                                JPG, PNG, WEBP (optional)
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
                            </label>
                        </div>

                        {/* ------------------- TITLE ------------------- */}
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Title <span className="text-red-400">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.title}
                                onChange={(e) =>
                                    setData("title", e.target.value)
                                }
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

                        {/* ------------------- MESSAGE ------------------- */}
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Message <span className="text-red-400">*</span>
                            </label>
                            <textarea
                                rows={5}
                                value={data.message}
                                onChange={(e) =>
                                    setData("message", e.target.value)
                                }
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.message
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            />
                            {errors.message && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.message}
                                </p>
                            )}
                        </div>

                        {/* ------------------- CHOICE RADIO ------------------- */}
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Send To
                            </label>
                            <div className="flex gap-6">
                                <label className="flex items-center gap-2 text-white cursor-pointer">
                                    <input
                                        type="radio"
                                        name="choice"
                                        value="all"
                                        checked={data.choice === "all"}
                                        onChange={(e) =>
                                            setData("choice", e.target.value)
                                        }
                                        className="w-4 h-4 accent-indigo-500"
                                    />
                                    All Users
                                </label>
                                <label className="flex items-center gap-2 text-white cursor-pointer">
                                    <input
                                        type="radio"
                                        name="choice"
                                        value="specific"
                                        checked={data.choice === "specific"}
                                        onChange={(e) =>
                                            setData("choice", e.target.value)
                                        }
                                        className="w-4 h-4 accent-indigo-500"
                                    />
                                    Specific User
                                </label>
                            </div>
                        </div>

                        {/* ------------------- USER SEARCH ------------------- */}
                        {data.choice === "specific" && (
                            <div className="relative">
                                <label className="block text-lg mb-2 text-white">
                                    Search User{" "}
                                    <span className="text-red-400">*</span>
                                </label>
                                {data.user_id ? (
                                    <div className="flex items-center justify-between px-3 py-2 rounded-lg bg-white/10 text-white">
                                        <span className="text-lg truncate">
                                            {searchTerm}
                                        </span>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setSearchTerm("");
                                                setData("user_id", "");
                                            }}
                                            className="ml-3 text-red-400 hover:text-red-500"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                ) : (
                                    <input
                                        type="text"
                                        value={searchTerm}
                                        onChange={(e) =>
                                            setSearchTerm(e.target.value)
                                        }
                                        placeholder="Search by name, ID card, or phone"
                                        className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                            ${
                                                errors.user_id
                                                    ? "ring-red-500"
                                                    : "focus:ring-indigo-400"
                                            }`}
                                    />
                                )}
                                {searchResults.length > 0 && (
                                    <ul className="absolute z-10 w-full bg-black/50 rounded-lg mt-1 max-h-60 overflow-auto shadow-lg pb-3 pt-3">
                                        {searchResults.map((u) => (
                                            <li
                                                key={u.id}
                                                onClick={() => {
                                                    setData("user_id", u.id);
                                                    setSearchTerm(
                                                        `${u.name} (${u.idcard})`
                                                    );
                                                    setSearchResults([]);
                                                }}
                                                className="px-3 py-2 hover:text-danger cursor-pointer border-b border-white/10 pb-3"
                                            >
                                                <div>
                                                    <h5>{u.name}</h5>
                                                    <span
                                                        style={{ opacity: 0.5 }}
                                                    >
                                                        {u.idcard} | {u.phone}
                                                    </span>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                                {errors.user_id && (
                                    <p className="text-red-400 text-xs mt-1">
                                        {errors.user_id}
                                    </p>
                                )}
                            </div>
                        )}

                        {/* ------------------- SUBMIT ------------------- */}
                        <div className="flex justify-end gap-3">
                            <Link href={route("notifications")}>
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
