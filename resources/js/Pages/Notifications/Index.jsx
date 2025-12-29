import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";
import ConfirmModal from "@/Components/ConfirmModal";

export default function Notifications({ user, notifications }) {
    const [data, setData] = useState(notifications.data ?? notifications);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);

    const [modalOpen, setModalOpen] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const perPage = 5;

    /* 🔍 Filter */
    const filtered = data.filter(
        (n) =>
            n.title.toLowerCase().includes(search.toLowerCase()) ||
            n.message.toLowerCase().includes(search.toLowerCase())
    );

    const totalPages = Math.ceil(filtered.length / perPage);
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    /* 🗑 Delete */
    const deleteNotification = () => {
        router.delete(`/notifications/${deleteId}`, {
            onSuccess: () => {
                setData((prev) => prev.filter((n) => n.id !== deleteId));
                setModalOpen(false);
            },
        });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div>
                {/* Header */}
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Notifications</h4>

                    <Link href={route("notifications.create")}>
                        <button className="bg-white/10 py-2 px-4 rounded-lg text-lg backdrop-blur-lg hover:bg-white/15 text-white">
                            + Create
                        </button>
                    </Link>
                </div>

                {/* TABLE CARD */}
                <div className="w-full overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    {/* Search */}
                    <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Search title or message..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>

                    {/* Scroll Area */}
                    <div
                        style={{
                            width: "1500px",
                            height: "53vh",
                            overflowY: "scroll",
                            overflowX: "scroll",
                            scrollBehavior: "smooth",
                            scrollbarColor: "#ffffff3d #ffffff00",
                        }}
                    >
                        <table className="table-fixed min-w-max w-full text-left text-sm">
                            <thead className="bg-white/5 border-b border-white/10 sticky top-0 z-10">
                                <tr>
                                    <th className="px-4 py-3 w-32 text-center">
                                        Image
                                    </th>
                                    <th className="px-4 py-3 w-40 text-center">
                                        Action
                                    </th>
                                    <th className="px-4 py-3 w-[300px]">Title</th>
                                    <th className="px-4 py-3 w-[300px]">Message</th>
                                    <th className="px-4 py-3 w-48 text-center">
                                        Recipient
                                    </th>
                                    <th className="px-4 py-3 w-32 text-center w-[200px]">
                                        Date
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((n, i) => (
                                    <motion.tr
                                        key={n.id}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.08 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        {/* Image */}
                                        <td className="px-4 py-3 text-center">
                                            {n.image ? (
                                                <img
                                                    src={n.image}
                                                    alt={n.title}
                                                    className="w-22 h-12 rounded-lg object-cover mx-auto"
                                                />
                                            ) : (
                                                <div className="w-22 h-12 bg-white/10 rounded-lg mx-auto flex items-center justify-center text-xs text-gray-400">
                                                    N/A
                                                </div>
                                            )}
                                        </td>

                                        {/* Actions */}
                                        <td className="px-4 py-3 flex justify-center gap-2">
                                            <button
                                                onClick={() =>
                                                    router.get(
                                                        "/notifications/edit/" +
                                                            n.id
                                                    )
                                                }
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                onClick={() => {
                                                    setModalOpen(true);
                                                    setDeleteId(n.id);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow"
                                            >
                                                Delete
                                            </button>
                                        </td>

                                        {/* Title */}
                                        <td className="px-4 py-3">{n.title}</td>

                                        {/* Message */}
                                        <td className="px-4 py-3 text-gray-300">{n.message}</td>

                                        {/* Recipient */}
                                        <td className="px-4 py-3 text-center">
                                            {n.recipient === "all" ? (
                                                <span className="px-3 py-2 rounded-lg text-xs bg-blue-500/30 text-blue-300">
                                                    All Users
                                                </span>
                                            ) : (
                                                <span className="px-3 py-2 rounded-lg text-xs bg-purple-500/30 text-purple-300">
                                                    {n.user ?? "Specific User"}
                                                </span>
                                            )}
                                        </td>

                                        {/* Date */}
                                        <td className="py-3 text-center w-60">
                                            {new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'long', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(n.created_at))}
                                        </td>
                                    </motion.tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="flex justify-between items-center p-4 bg-white/5 border-t border-white/10">
                        <button
                            onClick={() => setPage((p) => Math.max(1, p - 1))}
                            className="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs"
                        >
                            Previous
                        </button>

                        <span className="text-xs text-gray-300">
                            Page {page} of {totalPages}
                        </span>

                        <button
                            onClick={() =>
                                setPage((p) => Math.min(totalPages, p + 1))
                            }
                            className="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            {/* Confirm Delete Modal */}
            <ConfirmModal
                open={modalOpen}
                title="Delete Notification"
                message="Are you sure you want to delete this notification?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deleteNotification}
                onCancel={() => setModalOpen(false)}
            />
        </AuthenticatedLayout>
    );
}
