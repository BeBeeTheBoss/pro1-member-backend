import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useEffect, useState } from "react";
import SuccessIcon from "../../images/check.png";
import ErrorIcon from "../../images/delete.png";
import ConfirmModal from "@/Components/ConfirmModal";
import NotiMessage from "@/Components/NotiMessage";

const currentMonthRange = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const lastDay = String(new Date(year, now.getMonth() + 1, 0).getDate()).padStart(2, "0");

    return {
        from: `${year}-${month}-01`,
        to: `${year}-${month}-${lastDay}`,
    };
};

export default function Members({ user, members, filters = {} }) {
    const defaultExportRange = currentMonthRange();
    const [memberList, setMemberList] = useState(members.data ?? []);
    const [search, setSearch] = useState(filters.search ?? "");
    const [modelOpen, setModelOpen] = useState(false);
    const [memberIDForDelete, setMemberIDForDelete] = useState(null);
    const [notiOpen, setNotiOpen] = useState(false);
    const [fromDate, setFromDate] = useState(defaultExportRange.from);
    const [toDate, setToDate] = useState(defaultExportRange.to);
    const [exportError, setExportError] = useState("");

    const [notiType, setNotiType] = useState("");
    const [notiTitle, setNotiTitle] = useState("");
    const [notiMessage, setNotiMessage] = useState("");

    useEffect(() => {
        setMemberList(members.data ?? []);
    }, [members]);

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                "/members",
                { search },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    only: ["members", "filters"],
                }
            );
        }, 350);

        return () => clearTimeout(timer);
    }, [search]);

    const goToPage = (url) => {
        if (!url) return;

        router.get(
            url,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                only: ["members", "filters"],
            }
        );
    };

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

                if (response.props.flash.success) {
                    showNotification(response.props.flash.success, "success");
                }
            },
        });
    };

    const handleExport = (event) => {
        event.preventDefault();
        setExportError("");

        if (!fromDate || !toDate) {
            setExportError("Please select both From Date and To Date.");
            return;
        }

        if (fromDate > toDate) {
            setExportError("To Date must be the same as or later than From Date.");
            return;
        }

        window.location.assign(
            `/members/export?from_date=${encodeURIComponent(fromDate)}&to_date=${encodeURIComponent(toDate)}`
        );
    };

    return (
        <AuthenticatedLayout
            user={user}
            modelOpen={modelOpen}
            setModelOpen={setModelOpen}
        >
            <div>
                <div className="mb-4 flex flex-wrap items-end justify-between gap-4">
                    <h4 className="text-xl font-bold">Members</h4>
                    <form onSubmit={handleExport} className="flex flex-wrap items-end justify-end gap-3">
                        <label className="text-xs font-medium text-gray-300">
                            Start Date
                            <input
                                type="date"
                                value={fromDate}
                                onChange={(e) => setFromDate(e.target.value)}
                                className="mt-1 block rounded-lg border-white/20 bg-white/10 text-sm text-white shadow-sm"
                                required
                            />
                        </label>
                        <label className="text-xs font-medium text-gray-300">
                            End Date
                            <input
                                type="date"
                                value={toDate}
                                min={fromDate || undefined}
                                onChange={(e) => setToDate(e.target.value)}
                                className="mt-1 block rounded-lg border-white/20 bg-white/10 text-sm text-white shadow-sm"
                                required
                            />
                        </label>
                        <button
                            type="submit"
                            className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700"
                        >
                            Export Excel
                        </button>
                        {exportError && (
                            <p className="w-full text-right text-xs text-red-400">{exportError}</p>
                        )}
                    </form>
                </div>

                <div className="w-full overflow-hidden rounded-2xl mt-4 shadow-lg bg-dark bg-opacity-50">
                    {/* Search Box */}
                    <div className="p-4 border-b border-white/10 bg-white/5">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
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
                                {memberList.length > 0 ? (
                                    memberList.map((m, i) => (
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
                                                        setMemberIDForDelete(
                                                            m.id
                                                        );
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
                                            <td className="px-4 py-3">
                                                {m.phone}
                                            </td>
                                            <td className="px-4 py-3 capitalize">
                                                {m.gender}
                                            </td>
                                            <td className="px-4 py-3">
                                                {m.birth_date}
                                            </td>
                                            <td className="py-3 text-center w-60">
                                                {new Intl.DateTimeFormat(
                                                    "en-US",
                                                    {
                                                        year: "numeric",
                                                        month: "long",
                                                        day: "2-digit",
                                                        hour: "2-digit",
                                                        minute: "2-digit",
                                                    }
                                                ).format(
                                                    new Date(m.created_at)
                                                )}
                                            </td>
                                        </motion.tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="7"
                                            className="px-4 py-10 text-center text-gray-300"
                                        >
                                            No members found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="flex justify-between items-center p-4 bg-white/5 border-t border-white/10">
                        <button
                            onClick={() => goToPage(members.prev_page_url)}
                            disabled={!members.prev_page_url}
                            className="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>

                        <span className="text-xs text-gray-300">
                            Page {members.current_page ?? 1} of{" "}
                            {members.last_page ?? 1} ({members.total ?? 0}{" "}
                            members)
                        </span>

                        <button
                            onClick={() => goToPage(members.next_page_url)}
                            disabled={!members.next_page_url}
                            className="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs disabled:opacity-40 disabled:cursor-not-allowed"
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
