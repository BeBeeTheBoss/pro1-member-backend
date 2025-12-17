import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router, usePage } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";
import { useState } from "react";
import SuccessIcon from "../../images/check.png";
import ErrorIcon from "../../images/delete.png";
import ConfirmModal from "@/Components/ConfirmModal";
import NotiMessage from "@/Components/NotiMessage";

export default function Members({ user, members }) {
    const [memberList, setMemberList] = useState(members);
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");
    const [modelOpen, setModelOpen] = useState(false);
    const [memberIDForDelete, setMemberIDForDelete] = useState(null);
    const [notiOpen, setNotiOpen] = useState(false);

    const [notiType, setNotiType] = useState("");
    const [notiTitle, setNotiTitle] = useState("");
    const [notiMessage, setNotiMessage] = useState("");

    const perPage = 20;

    // Filter members by search
    const filtered = memberList.filter(
        (m) =>
            m.name.toLowerCase().includes(search.toLowerCase()) ||
            m.phone.includes(search) ||
            m.idcard.includes(search)
    );

    const totalPages = Math.ceil(filtered.length / perPage);
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const showNotification = (message, type) => {
        setNotiTitle(
            type === "success"
                ? "Congratulations!"
                : "Oops! Something went wrong."
        );
        setNotiType(type);
        setNotiMessage(message);
        setNotiOpen(true);
        setTimeout(() => setNotiOpen(false), 2000);
    };

    const handleDelete = () => {
        router.delete("/members/" + memberIDForDelete, {
            onSuccess: (response) => {
                setModelOpen(false);

                setMemberList((prev) =>
                    prev.filter((m) => m.id !== memberIDForDelete)
                );

                // 🔥 Fix pagination if last item on page removed
                setPage((p) => {
                    const newTotalPages = Math.ceil(
                        (filtered.length - 1) / perPage
                    );
                    return Math.min(p, newTotalPages === 0 ? 1 : newTotalPages);
                });

                if (response.props.flash.success) {
                    showNotification(response.props.flash.success, "success");
                }
            },
        });
    };

    return (
        <AuthenticatedLayout
            user={user}
            modelOpen={modelOpen}
            setModelOpen={setModelOpen}
        >
            <div>
                <h4 className="text-xl font-bold mb-4">Members</h4>

                <div className="w-full overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    {/* Search Box */}
                    <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Search by name, phone, or ID card..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 backdrop-blur-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-opacity-0"
                        />
                    </div>
                    <div
                        className=""
                        style={{
                            width: "1500px",
                            height: "53vh",
                            overflowY: "scroll",
                            overflowX: "scroll",
                            scrollBehavior: "smooth",
                            scrollbarColor: "#ffffff3d #ffffff00",
                        }}
                    >
                        <table className="w-full text-left text-sm">
                            <thead className="bg-white/5 border-b border-white/10">
                                <tr>
                                    <th className="px-4 py-3">Action</th>
                                    <th className="px-4 py-3">Name</th>
                                    <th className="px-4 py-3">ID Card</th>
                                    <th className="px-4 py-3">Phone</th>
                                    <th className="px-4 py-3">Gender</th>
                                    <th className="px-4 py-3">Birth Date</th>
                                    <th className="px-4 py-3">
                                        Registered Date
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((m, i) => (
                                    <motion.tr
                                        key={m.id}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.09 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        <td className="px-4 py-3">
                                            <button
                                                onClick={() => {
                                                    setModelOpen(true);
                                                    setMemberIDForDelete(m.id);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                        <td className="px-4 py-3 font-semibold flex items-center gap-3">
                                            {m.image ? (
                                                <img
                                                    src={m.image}
                                                    className="border border-white/10 w-12 h-12 rounded-full"
                                                />
                                            ) : (
                                                <div className="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center text-white fs-5 font-bold border">
                                                    {m.name
                                                        .charAt(0)
                                                        .toUpperCase()}
                                                </div>
                                            )}
                                            {m.name}
                                        </td>
                                        <td className="px-4 py-3">
                                            {m.idcard}
                                        </td>
                                        <td className="px-4 py-3">{m.phone}</td>
                                        <td className="px-4 py-3 capitalize">
                                            {m.gender}
                                        </td>
                                        <td className="px-4 py-3">
                                            {m.birth_date}
                                        </td>
                                        <td className="py-3 text-center w-60">
                                            {new Intl.DateTimeFormat("en-US", {
                                                year: "numeric",
                                                month: "long",
                                                day: "2-digit",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            }).format(new Date(m.created_at))}
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

            {/* Delete Modal */}
            {/* <AnimatePresence>
                {modelOpen && (
                    <motion.div
                        initial={{ opacity: 0, scale: 0.8 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.8 }}
                        transition={{ duration: 0.25 }}
                        className="w-90 bg-black/100 backdrop-blur-2xl shadow-xl rounded-2xl p-4 z-50"
                        style={{
                            position: "absolute",
                            top: "30%",
                            right: "43%",
                            transform: "translate(-50%, -50%)",
                            border: "1px solid #ffffff70",
                        }}
                    >
                        <h2 className="text-lg font-semibold mb-2 text-white">
                            Delete Member
                        </h2>

                        <p className="text-sm text-gray-300 mb-5">
                            Are you sure you want to delete this member? This
                            action cannot be undone.
                        </p>

                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => setModelOpen(false)}
                                className="px-4 py-2 rounded-xl bg-white/10 text-gray-200 hover:bg-white/20 transition"
                            >
                                Cancel
                            </button>

                            <button
                                onClick={handleDelete}
                                className="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 shadow"
                            >
                                Delete
                            </button>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence> */}

            <ConfirmModal
                open={modelOpen}
                title="Delete Member"
                message="Are you sure you want to delete this member? This action cannot be undone."
                onConfirm={handleDelete}
                onCancel={() => setModelOpen(false)}
                cancelText="Cancel"

            />
            <NotiMessage
                open={notiOpen}
                icon={notiType == "success" ? SuccessIcon : ErrorIcon}
                header={notiTitle}
                message={notiMessage}
            />
        </AuthenticatedLayout>
    );
}
