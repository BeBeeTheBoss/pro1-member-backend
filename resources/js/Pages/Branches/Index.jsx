import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Link } from "@inertiajs/react";
import ConfirmModal from "@/Components/ConfirmModal";
import { router } from "@inertiajs/react";
import SuccessIcon from "../../images/check.png";
import ErrorIcon from "../../images/delete.png";
import NotiMessage from "@/Components/NotiMessage";

export default function Branches({ user, branches }) {
    const [branchesList, setBranchesList] = useState(branches.data);

    const [modelOpen, setModelOpen] = useState(false);
    const [branchIDForDelete, setBranchIDForDelete] = useState(null);

    const [notiOpen, setNotiOpen] = useState(false);
    const [notiType, setNotiType] = useState("");
    const [notiTitle, setNotiTitle] = useState("");
    const [notiMessage, setNotiMessage] = useState("");

    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");

    const filtered = branchesList.filter(
        (b) =>
            b.name
                .toLowerCase()
                .replace(/\s/g, "")
                .includes(search.toLowerCase().replace(/\s/g, "")) ||
            b.region
                .toLowerCase()
                .replace(/\s/g, "")
                .includes(search.toLowerCase().replace(/\s/g, "")) ||
            b.township
                .toLowerCase()
                .replace(/\s/g, "")
                .includes(search.toLowerCase().replace(/\s/g, ""))
    );

    const perPage = 5;
    const totalPages = Math.ceil(filtered.length / perPage);
    const paginated = filtered.slice((page - 1) * perPage, page * perPage);

    const handleDelete = () => {
        router.delete("/branches/" + branchIDForDelete, {
            onSuccess: (response) => {
                setModelOpen(false);
                setBranchesList((prev) =>
                    prev.filter((b) => b.id !== branchIDForDelete)
                );
                if (response.props.flash.success) {
                    showNotification(response.props.flash.success, "success");
                }
            },
        });
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

    return (
        <AuthenticatedLayout user={user}>
            <div>
                <div className="flex justify-between px-2 items-center mb-4">
                    <h4 className="text-xl font-bold">Branches</h4>
                    <div>
                        <Link href={route("branches.create")}>
                            <button className="bg-white/10 py-2 px-4 rounded-lg text-lg backdrop-blur-lg hover:bg-white/15 text-white">
                                + Create
                            </button>
                        </Link>
                    </div>
                </div>

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
                        <table className="table-fixed min-w-max text-left text-sm">
                            <thead className="bg-white/5 border-b border-white/10">
                                <tr>
                                    <th className="px-4 py-3 w-32 text-center">
                                        Action
                                    </th>
                                    <th className="px-4 py-3 w-[200px]">
                                        Image
                                    </th>
                                    <th className="px-4 py-3 w-52">Name</th>
                                    <th className="px-4 py-3 w-96">Address</th>
                                    <th className="px-4 py-3 w-40">Contact</th>
                                    <th className="px-4 py-3 w-32">
                                        Opening time
                                    </th>
                                    <th className="px-4 py-3 w-32">
                                        Closing time
                                    </th>
                                    <th className="px-4 py-3 w-32">Latitude</th>
                                    <th className="px-4 py-3 w-32">
                                        Longitude
                                    </th>
                                    <th className="px-4 py-3 w-40">Region</th>
                                    <th className="px-4 py-3 w-40">Township</th>
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
                                        <td className="px-4 py-3 flex">
                                            <button
                                                onClick={() => router.get("/branches/edit/" + m.id)}
                                                className="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs shadow"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => {
                                                    setModelOpen(true);
                                                    setBranchIDForDelete(m.id);
                                                }}
                                                className="px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs shadow ms-2"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                        <td className="px-4 py-3 w-[200px] whitespace-nowrap">
                                            <img
                                                src={m.image}
                                                className="border border-white/10 rounded-md"
                                                style={{
                                                    width: 200,
                                                    height: 100,
                                                    objectFit: "cover",
                                                    objectPosition: "center",
                                                }}
                                            />
                                        </td>

                                        <td className="px-4 py-3 w-52 whitespace-nowrap">
                                            {m.name}
                                        </td>
                                        <td className="px-4 py-3 w-99">
                                            {m.address}
                                        </td>
                                        <td className="px-4 py-3 w-60">
                                            {m.contact}
                                        </td>
                                        <td className="px-4 py-3 w-32 whitespace-nowrap">
                                            {m.opening_time}
                                        </td>
                                        <td className="px-4 py-3 w-32 whitespace-nowrap">
                                            {m.closing_time}
                                        </td>
                                        <td className="px-4 py-3 w-32 whitespace-nowrap">
                                            {m.latitude}
                                        </td>
                                        <td className="px-4 py-3 w-32 whitespace-nowrap">
                                            {m.longitude}
                                        </td>
                                        <td className="px-4 py-3 w-40 whitespace-nowrap">
                                            {m.region}
                                        </td>
                                        <td className="px-4 py-3 w-40 whitespace-nowrap">
                                            {m.township}
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
                            position: "fixed", // changed from absolute
                            top: "50%", // center vertically
                            left: "50%", // center horizontally
                            transform: "translate(-50%, -50%)", // perfect center
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
                title="Delete Branch"
                message="Are you sure you want to delete this branch? This action cannot be undone."
                onConfirm={handleDelete}
                onCancel={() => setModelOpen(false)}
                cancelText="Cancel"
            />

            <NotiMessage
                open={notiOpen}
                icon={notiType === "success" ? SuccessIcon : ErrorIcon}
                header={notiTitle}
                message={notiMessage}
            />

        </AuthenticatedLayout>
    );
}
