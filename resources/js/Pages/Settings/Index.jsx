import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ConfirmModal from "@/Components/ConfirmModal";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";

export default function Settings({ user, settings }) {
    const [data, setData] = useState(settings.data ?? settings);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteAttribute, setDeleteAttribute] = useState(null);

    const perPage = 8;

    const filtered = data.filter((setting) => {
        const keyword = search.toLowerCase();

        return (
            setting.attribute.toLowerCase().includes(keyword) ||
            (setting.value ?? "").toLowerCase().includes(keyword)
        );
    });

    const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const deleteSetting = () => {
        router.delete(`/settings/${encodeURIComponent(deleteAttribute)}`, {
            onSuccess: () => {
                setData((prev) =>
                    prev.filter((setting) => setting.attribute !== deleteAttribute)
                );
                setModalOpen(false);
                setDeleteAttribute(null);
            },
        });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div>
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Settings</h4>
                    <Link href={route("settings.create")}>
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
                            placeholder="Search attribute or value..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>

                    <div
                        style={{
                            height: "53vh",
                            overflowY: "scroll",
                            overflowX: "auto",
                            scrollBehavior: "smooth",
                            scrollbarColor: "#ffffff3d #ffffff00",
                        }}
                    >
                        <table className="table-fixed min-w-full w-full text-left text-sm">
                            <thead className="bg-white/5 border-b border-white/10 sticky top-0 z-10">
                                <tr>
                                    <th className="px-4 py-3 w-40 text-center">
                                        Action
                                    </th>
                                    <th className="px-4 py-3 w-80">Attribute</th>
                                    <th className="px-4 py-3">Value</th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((setting, i) => (
                                    <motion.tr
                                        key={setting.attribute}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.05 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        <td className="px-4 py-3 flex justify-center">
                                            <button
                                                onClick={() =>
                                                    router.get(
                                                        `/settings/edit/${encodeURIComponent(setting.attribute)}`
                                                    )
                                                }
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setModalOpen(true);
                                                    setDeleteAttribute(setting.attribute);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow ms-2"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                        <td className="px-4 py-3 font-medium">
                                            {setting.attribute}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300 whitespace-pre-wrap break-words">
                                            {setting.value}
                                        </td>
                                    </motion.tr>
                                ))}

                                {paginated.length === 0 && (
                                    <tr>
                                        <td
                                            className="px-4 py-8 text-center text-gray-300"
                                            colSpan={3}
                                        >
                                            No settings found.
                                        </td>
                                    </tr>
                                )}
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
                title="Delete Setting"
                message="Are you sure you want to delete this setting?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deleteSetting}
                onCancel={() => setModalOpen(false)}
            />
        </AuthenticatedLayout>
    );
}
