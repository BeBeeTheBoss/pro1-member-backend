import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, router } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState } from "react";

export default function EditGamesEvent({ user, gameEvent, branches = [] }) {
    const [preview, setPreview] = useState(
        gameEvent.image ? `/storage/games-events/${gameEvent.image}` : null
    );
    const selectedBranchIds = gameEvent.branches?.map((branch) => branch.id) ?? [];

    const { data, setData, post, processing, errors, setError, clearErrors } = useForm({
        id: gameEvent.id,
        name: gameEvent.name ?? "",
        description: gameEvent.description ?? "",
        image: null,
        type: gameEvent.type ?? "",
        minimum_purchase_amount: gameEvent.minimum_purchase_amount ?? "",
        start_date: gameEvent.start_date ?? "",
        start_time: gameEvent.start_time?.slice(0, 5) ?? "",
        end_date: gameEvent.end_date ?? "",
        end_time: gameEvent.end_time?.slice(0, 5) ?? "",
        is_active: !!gameEvent.is_active,
        all_branches: !!gameEvent.all_branches,
        branch_ids: selectedBranchIds,
    });

    const toggleBranch = (branchId) => {
        const nextBranchIds = data.branch_ids.includes(branchId)
            ? data.branch_ids.filter((id) => id !== branchId)
            : [...data.branch_ids, branchId];

        setData("branch_ids", nextBranchIds);
    };

    const submit = (e) => {
        e.preventDefault();

        clearErrors();
        let hasError = false;

        if (!data.name.trim()) {
            setError("name", "Name is required");
            hasError = true;
        }

        if (!data.type.trim()) {
            setError("type", "Type is required");
            hasError = true;
        }

        if (data.minimum_purchase_amount !== "" && Number(data.minimum_purchase_amount) < 0) {
            setError("minimum_purchase_amount", "Minimum purchase amount must be 0 or greater");
            hasError = true;
        }

        if (!data.all_branches && data.branch_ids.length === 0) {
            setError("branch_ids", "Please choose at least one branch");
            hasError = true;
        }

        if (hasError) return;

        post("/games-events/update", { forceFormData: true });
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">Edit Games Event</h4>
                </div>

                <button
                    className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15"
                    onClick={() => router.get("/games-events")}
                >
                    <span className="text-white text-lg">Back</span>
                </button>

                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block text-lg mb-2 text-white">Name <span className="text-red-400">*</span></label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData("name", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.name && <p className="text-red-400 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">Type <span className="text-red-400">*</span></label>
                            <input
                                type="text"
                                value={data.type}
                                onChange={(e) => setData("type", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.type && <p className="text-red-400 text-xs mt-1">{errors.type}</p>}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">Minimum Purchase Amount</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                value={data.minimum_purchase_amount}
                                onChange={(e) => setData("minimum_purchase_amount", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.minimum_purchase_amount && <p className="text-red-400 text-xs mt-1">{errors.minimum_purchase_amount}</p>}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">Description</label>
                            <textarea
                                rows={4}
                                value={data.description}
                                onChange={(e) => setData("description", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                        </div>

                        <div className="space-y-4">
                            <div>
                                <label className="block text-lg mb-2 text-white">Date Range</label>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input
                                        type="date"
                                        value={data.start_date}
                                        onChange={(e) => setData("start_date", e.target.value)}
                                        className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    />
                                    <input
                                        type="date"
                                        value={data.end_date}
                                        onChange={(e) => setData("end_date", e.target.value)}
                                        className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-lg mb-2 text-white">Daily Time Window</label>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input
                                        type="time"
                                        value={data.start_time}
                                        onChange={(e) => setData("start_time", e.target.value)}
                                        className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    />
                                    <input
                                        type="time"
                                        value={data.end_time}
                                        onChange={(e) => setData("end_time", e.target.value)}
                                        className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    />
                                </div>
                            </div>
                        </div>

                        <div>
                            <div className="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <label className="block text-lg text-white">Applied Branches</label>
                                    {errors.branch_ids && <p className="text-red-400 text-xs mt-1">{errors.branch_ids}</p>}
                                </div>

                                <label className="flex items-center gap-2 text-sm text-white cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.all_branches}
                                        onChange={(e) => {
                                            setData({
                                                ...data,
                                                all_branches: e.target.checked,
                                                branch_ids: e.target.checked ? [] : data.branch_ids,
                                            });
                                        }}
                                        className="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-indigo-400"
                                    />
                                    All branches
                                </label>
                            </div>

                            {!data.all_branches && (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-56 overflow-y-auto rounded-lg bg-white/5 p-3">
                                    {branches.map((branch) => (
                                        <label key={branch.id} className="flex items-center gap-2 text-sm text-white">
                                            <input
                                                type="checkbox"
                                                checked={data.branch_ids.includes(branch.id)}
                                                onChange={() => toggleBranch(branch.id)}
                                                className="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-indigo-400"
                                            />
                                            <span>
                                                {branch.name}{branch.branch_code ? ` (${branch.branch_code})` : ""}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="flex items-center gap-3">
                            <label className="relative inline-block w-14 h-7 cursor-pointer">
                                <input
                                    type="checkbox"
                                    className="peer sr-only"
                                    checked={data.is_active}
                                    onChange={(e) => setData("is_active", e.target.checked)}
                                />
                                <span className="absolute inset-0 bg-gray-300 rounded-lg transition peer-checked:bg-green-500"></span>
                                <span className="absolute left-1 bottom-1 w-5 h-5 bg-white rounded-md transition peer-checked:translate-x-6"></span>
                            </label>
                            <span className="text-white text-sm">Active</span>
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">Image</label>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={(e) => {
                                    const file = e.target.files[0];
                                    setData("image", file);
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onloadend = () => setPreview(reader.result);
                                        reader.readAsDataURL(file);
                                    } else {
                                        setPreview(
                                            gameEvent.image
                                                ? `/storage/games-events/${gameEvent.image}`
                                                : null
                                        );
                                    }
                                }}
                                className="w-full text-sm"
                            />
                            {errors.image && <p className="text-red-400 text-xs mt-1">{errors.image}</p>}
                            {preview && (
                                <img
                                    src={preview}
                                    alt="Preview"
                                    className="mt-3 w-48 h-48 object-cover rounded-lg border border-white/10"
                                />
                            )}
                        </div>

                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => router.get("/games-events")}
                                className="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white disabled:opacity-50"
                            >
                                Update
                            </button>
                        </div>
                    </form>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
