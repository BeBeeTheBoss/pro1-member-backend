import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";
import ConfirmModal from "@/Components/ConfirmModal";

export default function Events({ user, events }) {
    const [data, setData] = useState(events.data ?? events);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);

    const [modalOpen, setModalOpen] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const perPage = 5;

    const filtered = data.filter((e) => {
        const name = e.name?.toLowerCase() ?? "";
        const desc = e.description?.toLowerCase() ?? "";
        return name.includes(search.toLowerCase()) || desc.includes(search.toLowerCase());
    });

    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const deleteEvent = () => {
        router.delete(`/events/${deleteId}`, {
            onSuccess: () => {
                setData((prev) => prev.filter((e) => e.id !== deleteId));
                setModalOpen(false);
            },
        });
    };

    const formatRange = (startDate, startTime, endDate, endTime) => {
        if (!startDate && !endDate) return "-";

        const start = [startDate, formatTime(startTime)].filter(Boolean).join(" ");
        const end = [endDate, formatTime(endTime)].filter(Boolean).join(" ");

        return `${start || "-"} to ${end || "-"}`;
    };

    const formatTime = (time) => {
        return time ? time.slice(0, 5) : null;
    };

    return (
        <AuthenticatedLayout user={user}>
            <div>
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Events</h4>
                    <div>
                        <Link href={route("events.create")}>
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
                            placeholder="Search event name or description..."
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
                                    <th className="px-4 py-3 w-40 text-center">Image</th>
                                    <th className="px-4 py-3 w-64">Name</th>
                                    <th className="px-4 py-3 w-80">Date & Time Range</th>
                                    <th className="px-4 py-3 w-[600px]">Description</th>
                                    <th className="px-4 py-3 w-[500px]">Platforms</th>
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
                                                onClick={() =>
                                                    router.get(`/events/edit/${e.id}`)
                                                }
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
                                                    src={`/storage/events/${e.image}`}
                                                    alt={e.name}
                                                    className="w-16 h-16 object-cover rounded-lg border border-white/10 mx-auto"
                                                />
                                            ) : (
                                                <span className="text-gray-400 text-xs">No image</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">{e.name}</td>
                                        <td className="px-4 py-3 text-gray-300 whitespace-nowrap">
                                            {formatRange(
                                                e.start_date,
                                                e.start_time,
                                                e.end_date,
                                                e.end_time
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {e.description ?? "-"}
                                        </td>
                                        <td className="px-4 py-3 text-gray-300">
                                            {(e.platform_links || []).map((p) => (
                                                <div key={p.id} className="mb-1">
                                                    <span className="text-white">
                                                        {p.platform?.name ?? "-"}
                                                    </span>
                                                    <span className="text-gray-400"> - </span>
                                                    <a
                                                        href={p.link}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="text-indigo-300 hover:text-indigo-200"
                                                    >
                                                        {p.link}
                                                    </a>
                                                </div>
                                            ))}
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
                title="Delete Event"
                message="Are you sure you want to delete this event?"
                confirmText="Delete"
                cancelText="Cancel"
                onConfirm={deleteEvent}
                onCancel={() => setModalOpen(false)}
            />
        </AuthenticatedLayout>
    );
}
