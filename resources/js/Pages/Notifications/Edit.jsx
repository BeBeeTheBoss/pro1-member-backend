import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState, useEffect } from "react";
import axios from "axios";

export default function CreateNotification({ user ,notification}) {
    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            id: notification.id,
            title: notification.title,
            message: notification.message,
            choice: notification.recipient, // all or specific
            user_id: notification.user?.id, // for single choice
        });

    const [searchTerm, setSearchTerm] = useState(notification.user ? `${notification.user?.name} (${notification.user?.phone})` : "");
    const [searchResults, setSearchResults] = useState([]);

    // Simulated API call to search users
    const searchUsers = async (query) => {
        if (!query) return setSearchResults([]);
        try {
            console.log(query);

            const response = await axios.get("/api/users/search", {
                params: {
                    searchKey: query,
                },
            });
            console.log(response);

            setSearchResults(response.data); // Expect array [{id, name, phone, id_card}]
        } catch (error) {
            console.error(error);
        }
    };

    useEffect(() => {
        const timeout = setTimeout(() => {
            searchUsers(searchTerm);
        }, 300); // debounce
        return () => clearTimeout(timeout);
    }, [searchTerm]);

    const submit = (e) => {
        e.preventDefault();
        let hasError = false;
        clearErrors();

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

        post("/notifications/update");
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">
                        Create Notification
                    </h4>
                </div>

                <button className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15" onClick={() => router.get('/notifications')}>
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
                        {/* Title */}
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

                        {/* Message */}
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

                        {/* Choice Radio */}
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

                        {/* Searchable User Input */}
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

                                        {/* Clear selection */}
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
                                {/* Search results dropdown */}
                                {searchResults.length > 0 && (
                                    <ul
                                        className="absolute z-10 w-full bg-black/50 rounded-lg mt-1 max-h-60 overflow-auto shadow-lg pb-3 pt-3"
                                        style={{
                                            maxHeight: "200px",
                                            overflow: "scroll",
                                            scrollbarColor: "#3a3a3aff black",
                                        }}
                                    >
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

                        {/* Submit */}
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
