import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";
import ConfirmModal from "@/Components/ConfirmModal";

export default function Privileges({ user, privileges }) {
    const [data, setData] = useState(privileges.data ?? privileges);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);

    const [modalOpen, setModalOpen] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const perPage = 5;

    const filtered = data.filter((p) => {
        const title = p.title?.toLowerCase() ?? "";
        const desc = p.description?.toLowerCase() ?? "";
        const category = p.category?.name?.toLowerCase() ?? "";
        return (
            title.includes(search.toLowerCase()) ||
            desc.includes(search.toLowerCase()) ||
            category.includes(search.toLowerCase())
        );
    });

    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const deletePrivilege = () => {
        router.delete(`/privileges/${deleteId}`, {
            onSuccess: () => {
                setData((prev) => prev.filter((p) => p.id !== deleteId));
                setModalOpen(false);
            },
        });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div>
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Privileges</h4>
                    <div>
                        <Link href={route("privileges.create")}>
                            <button className="bg-white/10 py-2 px-4 rounded-lg text-lg backdrop-blur-lg hover:bg-white/15 text-white">
                                + Create
                            </button>
                        </Link>
                    </div>
                </div>

                <div className="w-full overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Search title, description or category..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>

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
                                    <th className="px-4 py-3 w-40 text-center">Action</th>
                                    <th className="px-4 py-3 w-48 text-center">Image</th>
                                    <th className="px-4 py-3 w-64">Title</th>
                                    <th className="px-4 py-3 w-48">Category</th>
                                    <th className="px-4 py-3 w-64">Date Range</th>
                                    <th className="px-4 py-3 w-[600px]">Description</th>
                                    <th className="px-4 py-3 w-32 text-center">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((p, i) => (
                                    <motion.tr
                                        key={p.id}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.08 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        <td className="px-4 py-3 flex">
                                            <button
                                                onClick={() =>
                                                    router.get(`/privileges/edit/${p.id}`)
                                                }
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setModalOpen(true);
                                                    setDeleteId(p.id);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow ms-2"
                                            >
                                                Delete
                                            </button>
                                        </td>

                                        <td className="px-4 py-3 text-center">
                                            {p.image ? (
                                                <img
                                                    src={`/storage/privileges/${p.image}`}
                                                    alt={p.title}
                                                    className="w-16 h-16 object-cover rounded-lg border border-white/10 mx-auto"
                                                />
                                            ) : (
                                                <span className="text-gray-400 text-xs">No image</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">{p.title}</td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {p.category?.name ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {(p.start_date || p.end_date)
                                                ? `${p.start_date ?? "-"} to ${p.end_date ?? "-"}`
                                                : "-"}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {p.description ?? "-"}
                                        </td>

                                        <td className="px-4 py-3 text-center">
                                            <button
                                                className={`px-2 py-1 rounded-full text-xs ${
                                                    p.is_active
                                                        ? "bg-green-500 hover:bg-green-600"
                                                        : "bg-red-500 hover:bg-red-600"
                                                }`}
                                            >
                                                {p.is_active ? "Active" : "Inactive"}
                                            </button>
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
                title="Delete Privilege"
                message="Are you sure you want to delete this privilege?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deletePrivilege}
                onCancel={() => setModalOpen(false)}
            />
        </AuthenticatedLayout>
    );
}
