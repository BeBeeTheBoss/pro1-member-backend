import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";
import ConfirmModal from "@/Components/ConfirmModal";

export default function SpinWheelChances({ user, chances }) {
    const [data, setData] = useState(chances.data ?? chances);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const perPage = 10;

    const filtered = data.filter(
        (item) =>
            String(item.points).includes(search) ||
            String(item.max_times).includes(search) ||
            String(item.type ?? "").toLowerCase().includes(search.toLowerCase())
    );

    const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const deleteChance = () => {
        router.delete(`/spin-wheel-chances/${deleteId}`, {
            onSuccess: () => {
                setData((prev) => prev.filter((item) => item.id !== deleteId));
                setModalOpen(false);
            },
        });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div>
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Spin Wheel Chances</h4>
                    <Link href={route("spin-wheel-chances.create")}>
                        <button className="bg-white/10 py-2 px-4 rounded-lg text-lg backdrop-blur-lg hover:bg-white/15 text-white">
                            + Create
                        </button>
                    </Link>
                </div>

                <div className="w-full overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Search points or max times..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>

                    <div
                        style={{
                            width: "100%",
                            minHeight: "53vh",
                            maxHeight: "53vh",
                            overflowY: "auto",
                            overflowX: "auto",
                            scrollbarColor: "#ffffff3d #ffffff00",
                        }}
                    >
                        <table className="table-fixed min-w-full w-full text-left text-sm">
                            <thead className="bg-white/5 border-b border-white/10 sticky top-0 z-10">
                                <tr>
                                    <th className="px-4 py-3 w-40 text-center">Action</th>
                                    <th className="px-4 py-3">Points</th>
                                    <th className="px-4 py-3">Max Times</th>
                                    <th className="px-4 py-3">Type</th>
                                    <th className="px-4 py-3">Created At</th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((item, i) => (
                                    <motion.tr
                                        key={item.id}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.05 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        <td className="px-4 py-3 flex gap-2 justify-center">
                                            <button
                                                onClick={() =>
                                                    router.get(`/spin-wheel-chances/edit/${item.id}`)
                                                }
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setDeleteId(item.id);
                                                    setModalOpen(true);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                        <td className="px-4 py-3">{item.points}</td>
                                        <td className="px-4 py-3">{item.max_times}</td>
                                        <td className="px-4 py-3">
                                            {item.type ?? "-"}
                                        </td>
                                        <td className="px-4 py-3">
                                            {new Date(item.created_at).toLocaleString()}
                                        </td>
                                    </motion.tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

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
                            onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                            className="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <ConfirmModal
                open={modalOpen}
                title="Delete Spin Chance"
                message="Are you sure you want to delete this spin chance?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deleteChance}
                onCancel={() => setModalOpen(false)}
            />
        </AuthenticatedLayout>
    );
}
