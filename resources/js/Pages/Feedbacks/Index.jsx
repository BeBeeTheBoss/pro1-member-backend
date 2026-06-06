import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { motion } from "framer-motion";
import { useMemo, useState } from "react";

const formatDateTime = (dateTime) => {
    if (!dateTime) {
        return "-";
    }

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(dateTime));
};

const formatDate = (date) => {
    if (!date) {
        return "-";
    }

    return String(date).slice(0, 10);
};

export default function Feedbacks({ user, feedbacks }) {
    const rows = feedbacks.data ?? feedbacks;
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);

    const perPage = 8;

    const filtered = useMemo(() => {
        const keyword = search.toLowerCase();

        return rows.filter((feedback) => {
            const userName = feedback.user?.name ?? "";
            const userPhone = feedback.user?.phone ?? "";
            const branchName = feedback.branch?.name ?? "";
            const branchCode = feedback.branch?.branch_code ?? "";
            const matchesKeyword = [
                feedback.message,
                branchName,
                branchCode,
                userName,
                userPhone,
            ]
                .filter(Boolean)
                .some((value) => value.toLowerCase().includes(keyword));
            return matchesKeyword;
        });
    }, [rows, search]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    return (
        <AuthenticatedLayout user={user}>
            <div className="space-y-6">
                <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h4 className="text-2xl font-bold text-white">Feedbacks</h4>
                        <p className="mt-1 text-sm text-gray-300">
                            Review member feedback by branch.
                        </p>
                    </div>
                </div>

                <div className="overflow-hidden rounded-lg border border-white/10 bg-white/[0.04]">
                    <div className="border-b border-white/10 bg-white/[0.04] p-4">
                        <input
                            value={search}
                            onChange={(event) => {
                                setSearch(event.target.value);
                                setPage(1);
                            }}
                            placeholder="Search feedback, branch, name, or phone..."
                            className="w-full rounded-lg bg-white/10 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                    </div>

                    <div className="max-h-[58vh] overflow-auto">
                        <table className="w-full min-w-[920px] text-left text-sm">
                            <thead className="sticky top-0 z-10 bg-slate-950/95 text-xs uppercase text-gray-400">
                                <tr>
                                    <th className="px-4 py-3 w-56">Member</th>
                                    <th className="px-4 py-3 w-48">Branch</th>
                                    <th className="px-4 py-3 w-32">Date</th>
                                    <th className="px-4 py-3 w-24 text-center">
                                        Rating
                                    </th>
                                    <th className="px-4 py-3 w-[320px]">
                                        Feedback
                                    </th>
                                    <th className="px-4 py-3 w-48">Images</th>
                                    <th className="px-4 py-3 w-40">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {paginated.length > 0 ? (
                                    paginated.map((feedback, index) => (
                                        <motion.tr
                                            key={feedback.id}
                                            initial={{ opacity: 0, y: 8 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            transition={{ delay: index * 0.04 }}
                                            className="border-t border-white/5 align-top hover:bg-white/[0.04]"
                                        >
                                            <td className="px-4 py-3">
                                                <p className="font-semibold text-white">
                                                    {feedback.user?.name ?? "Unknown"}
                                                </p>
                                                <p className="text-xs text-gray-400">
                                                    {feedback.user?.phone ?? "-"}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    {feedback.user?.idcard ?? "-"}
                                                </p>
                                            </td>
                                            <td className="px-4 py-3 text-gray-200">
                                                <p className="font-semibold text-white">
                                                    {feedback.branch?.name ?? "-"}
                                                </p>
                                                <p className="text-xs text-gray-400">
                                                    {feedback.branch?.branch_code ?? "-"}
                                                </p>
                                            </td>
                                            <td className="px-4 py-3 text-gray-300">
                                                {formatDate(feedback.date)}
                                            </td>
                                            <td className="px-4 py-3 text-center text-gray-200">
                                                {feedback.rating}/5
                                            </td>
                                            <td className="px-4 py-3">
                                                <p className="text-gray-300">{feedback.message}</p>
                                            </td>
                                            <td className="px-4 py-3">
                                                {feedback.images?.length > 0 ? (
                                                    <div className="flex flex-wrap gap-2">
                                                        {feedback.images.map((image) => (
                                                            <a
                                                                key={image.id}
                                                                href={image.image_url}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                            >
                                                                <img
                                                                    src={image.image_url}
                                                                    alt="Feedback attachment"
                                                                    className="h-14 w-14 rounded-lg object-cover"
                                                                />
                                                            </a>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-gray-500">
                                                        No images
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-gray-300">
                                                {formatDateTime(feedback.created_at)}
                                            </td>
                                        </motion.tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="7"
                                            className="px-4 py-10 text-center text-gray-400"
                                        >
                                            No feedbacks found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex items-center justify-between border-t border-white/10 bg-white/[0.04] p-4">
                        <button
                            onClick={() => setPage((current) => Math.max(1, current - 1))}
                            className="rounded-lg bg-white/10 px-3 py-1 text-xs hover:bg-white/20"
                        >
                            Previous
                        </button>
                        <span className="text-xs text-gray-300">
                            Page {page} of {totalPages}
                        </span>
                        <button
                            onClick={() =>
                                setPage((current) =>
                                    Math.min(totalPages, current + 1)
                                )
                            }
                            className="rounded-lg bg-white/10 px-3 py-1 text-xs hover:bg-white/20"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
