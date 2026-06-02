import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";
import ConfirmModal from "@/Components/ConfirmModal";

export default function GamesEvents({ user, gamesEvents }) {
    const [data, setData] = useState(gamesEvents.data ?? gamesEvents);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const perPage = 5;

    const filtered = data.filter((e) => {
        const name = e.name?.toLowerCase() ?? "";
        const desc = e.description?.toLowerCase() ?? "";
        const type = e.type?.toLowerCase() ?? "";
        const minimumPurchaseAmount = e.minimum_purchase_amount?.toString() ?? "";
        const branches = e.all_branches
            ? "all branches"
            : e.branches?.map((branch) => branch.name).join(" ").toLowerCase() ?? "";
        const keyword = search.toLowerCase();
        return name.includes(keyword) || desc.includes(keyword) || type.includes(keyword) || branches.includes(keyword) || minimumPurchaseAmount.includes(keyword);
    });

    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const deleteEvent = () => {
        router.delete(`/games-events/${deleteId}`, {
            onSuccess: () => {
                setData((prev) => prev.filter((e) => e.id !== deleteId));
                setModalOpen(false);
            },
        });
    };

    const formatRange = (start, end) => {
        if (!start && !end) return "-";

        return `${start ?? "-"} to ${end ?? "-"}`;
    };

    const formatTime = (time) => {
        return time ? time.slice(0, 5) : null;
    };

    const formatAmount = (amount) => {
        if (amount === null || amount === undefined || amount === "") return "-";

        return Number(amount).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    const formatBranches = (event) => {
        if (event.all_branches) return "All branches";

        return event.branches?.length
            ? event.branches
                  .map((branch) => `${branch.name}${branch.branch_code ? ` (${branch.branch_code})` : ""}`)
                  .join(", ")
            : "-";
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="min-w-0">
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Games Events</h4>
                    <div>
                        <Link href={route("games-events.create")}>
                            <button className="bg-white/10 py-2 px-4 rounded-lg text-lg backdrop-blur-lg hover:bg-white/15 text-white">
                                + Create
                            </button>
                        </Link>
                    </div>
                </div>

                <div className="w-full min-w-0 overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Search name, type, branch or description..."
                            className="w-full px-3 py-2 rounded-lg bg-white/10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>

                    <div
                        className="w-full"
                        style={{
                            height: "53vh",
                            overflowY: "auto",
                            overflowX: "auto",
                            scrollBehavior: "smooth",
                            scrollbarColor: "#ffffff3d #ffffff00",
                        }}
                    >
                        <table className="table-fixed min-w-[1750px] w-full text-left text-sm">
                            <thead className="bg-white/5 border-b border-white/10 sticky top-0 z-10">
                                <tr>
                                    <th className="px-4 py-3 w-40 text-center">Action</th>
                                    <th className="px-4 py-3 w-40 text-center">Image</th>
                                    <th className="px-4 py-3 w-56">Name</th>
                                    <th className="px-4 py-3 w-40">Type</th>
                                    <th className="px-4 py-3 w-48">Minimum Purchase</th>
                                    <th className="px-4 py-3 w-64">Date Range</th>
                                    <th className="px-4 py-3 w-56">Daily Time Window</th>
                                    <th className="px-4 py-3 w-72">Applied Branches</th>
                                    <th className="px-4 py-3 w-32 text-center">Status</th>
                                    <th className="px-4 py-3 w-[500px]">Description</th>
                                </tr>
                            </thead>

                            <tbody>
                                {paginated.map((e, i) => (
                                    <motion.tr
                                        key={e.id}
                                        initial={{ opacity: 0, y: 10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: i * 0.08 }}
                                        className="hover:bg-white/5 transition-colors border-b border-white/5"
                                    >
                                        <td className="px-4 py-3 flex">
                                            <button
                                                onClick={() => router.get(`/games-events/edit/${e.id}`)}
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setModalOpen(true);
                                                    setDeleteId(e.id);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow ms-2"
                                            >
                                                Delete
                                            </button>
                                        </td>

                                        <td className="px-4 py-3 text-center">
                                            {e.image ? (
                                                <img
                                                    src={`/storage/games-events/${e.image}`}
                                                    alt={e.name}
                                                    className="w-16 h-16 object-cover rounded-lg border border-white/10 mx-auto"
                                                />
                                            ) : (
                                                <span className="text-gray-400 text-xs">No image</span>
                                            )}
                                        </td>

                                        <td className="px-4 py-3">{e.name}</td>
                                        <td className="px-4 py-3">{e.type}</td>
                                        <td className="px-4 py-3 text-gray-300">{formatAmount(e.minimum_purchase_amount)}</td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {formatRange(e.start_date, e.end_date)}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {formatRange(formatTime(e.start_time), formatTime(e.end_time))}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">{formatBranches(e)}</td>
                                        <td className="px-4 py-3 text-center">
                                            <span
                                                className={`px-2 py-1 rounded-full text-xs ${
                                                    e.is_active
                                                        ? "bg-green-500 hover:bg-green-600"
                                                        : "bg-red-500 hover:bg-red-600"
                                                }`}
                                            >
                                                {e.is_active ? "Active" : "Inactive"}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">{e.description ?? "-"}</td>
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
                title="Delete Games Event"
                message="Are you sure you want to delete this games event?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deleteEvent}
                onCancel={() => setModalOpen(false)}
            />
        </AuthenticatedLayout>
    );
}
