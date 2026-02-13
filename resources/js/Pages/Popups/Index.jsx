import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import ConfirmModal from "@/Components/ConfirmModal";
import NotiMessage from "@/Components/NotiMessage";
import SuccessIcon from "../../images/check.png";
import ErrorIcon from "../../images/delete.png";

export default function FAQs({ user, popups }) {
    const [data, setData] = useState(popups.data);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);

    const [modalOpen, setModalOpen] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const [notiOpen, setNotiOpen] = useState(false);
    const [notiType, setNotiType] = useState("");
    const [notiTitle, setNotiTitle] = useState("");
    const [notiMessage, setNotiMessage] = useState("");

    const perPage = 5;

    // 🔍 Filter
    // const filtered = data.filter(
    //     (f) =>
    //         f.start.toLowerCase().includes(search.toLowerCase()) ||
    //         f.answer.toLowerCase().includes(search.toLowerCase()),
    // );

    const totalPages = Math.ceil(data.length / perPage);
    const paginated = data.slice((page - 1) * perPage, page * perPage);

    // 🗑 Delete
    const deletePopup = () => {
        router.delete(`/popups/${deleteId}`, {
            onSuccess: () => {
                setData((prev) => prev.filter((f) => f.id !== deleteId));
                setModalOpen(false);
                showNotification("Popup deleted successfully.", "success");
            },
        });

    };

    const showNotification = (message, type) => {
        setNotiTitle(
            type === "success"
                ? "Congratulations!"
                : "Oops! Something went wrong.",
        );
        setNotiType(type);
        setNotiMessage(message);
        setNotiOpen(true);
        setTimeout(() => setNotiOpen(false), 2000);
    };

    return (
        <AuthenticatedLayout user={user}>
            <div>
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Popups</h4>
                    <div>
                        <Link href={route("popups.create")}>
                            <button className="bg-white/10 py-2 px-4 rounded-lg text-lg backdrop-blur-lg hover:bg-white/15 text-white">
                                + Create
                            </button>
                        </Link>
                    </div>
                </div>
                <div className="w-full overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    {/* Search */}
                    {/* <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Search question or answer..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div> */}

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
                                    <th className="px-4 py-3 w-40 text-center">
                                        Action
                                    </th>
                                    <th className="px-4 py-3 w-96">Image</th>
                                    <th className="px-4 py-3 w-96">
                                        Start Date
                                    </th>
                                    <th className="px-4 py-3">End Date</th>
                                    <th className="px-4 py-3 w-32 text-center">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((f, i) => (
                                    <motion.tr
                                        key={f.id}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.08 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        <td className="px-4 py-3 flex">
                                            <button
                                                onClick={() =>
                                                    router.get(
                                                        "/popups/edit/" + f.id,
                                                    )
                                                }
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setModalOpen(true);
                                                    setDeleteId(f.id);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow ms-2"
                                            >
                                                Delete
                                            </button>
                                        </td>

                                        <td className="px-4 py-3">
                                            <img
                                                src={f.image}
                                                className="w-20 h-20 object-cover"
                                            />
                                            {/* {f.image} */}
                                        </td>

                                        <td className="px-4 py-3">
                                            {f.start_date}
                                        </td>

                                        <td className="px-4 py-3 text-gray-300">
                                            {f.end_date}
                                        </td>

                                        <td className="px-4 py-3 text-center">
                                            <button
                                                className={`px-2 py-1 rounded-full text-xs ${
                                                    f.is_active
                                                        ? "bg-green-500 hover:bg-green-600"
                                                        : "bg-red-500 hover:bg-red-600"
                                                }`}
                                            >
                                                {f.is_active
                                                    ? "Active"
                                                    : "Inactive"}
                                            </button>
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
            <ConfirmModal
                open={modalOpen}
                title="Delete Popup"
                message="Are you sure you want to delete this popup?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deletePopup}
                onCancel={() => setModalOpen(false)}
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
