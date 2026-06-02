import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Link, useForm } from "@inertiajs/react";
import { motion } from "framer-motion";

export default function EditSpinWheelChance({ user, chance }) {
    const { data, setData, post, processing, errors } = useForm({
        id: chance.id,
        points: chance.points,
        max_times: chance.max_times,
        type: chance.type ?? "other",
    });

    const submit = (e) => {
        e.preventDefault();
        post("/spin-wheel-chances/update");
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">Edit Spin Wheel Chance</h4>
                </div>

                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Points <span className="text-red-400">*</span>
                            </label>
                            <input
                                type="number"
                                min="1"
                                value={data.points}
                                onChange={(e) => setData("points", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.points && (
                                <p className="text-red-400 text-xs mt-1">{errors.points}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Max Times <span className="text-red-400">*</span>
                            </label>
                            <input
                                type="number"
                                min="0"
                                value={data.max_times}
                                onChange={(e) => setData("max_times", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            />
                            {errors.max_times && (
                                <p className="text-red-400 text-xs mt-1">{errors.max_times}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Type <span className="text-red-400">*</span>
                            </label>
                            <select
                                value={data.type}
                                onChange={(e) => setData("type", e.target.value)}
                                className="w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            >
                                <option value="super_prize" className="text-black">super_prize</option>
                                <option value="fix_prize" className="text-black">fix_prize</option>
                                <option value="other" className="text-black">other</option>
                            </select>
                            {errors.type && (
                                <p className="text-red-400 text-xs mt-1">{errors.type}</p>
                            )}
                        </div>

                        <div className="flex justify-end gap-3">
                            <Link href={route("spin-wheel-chances")}>
                                <button
                                    type="button"
                                    className="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white"
                                >
                                    Cancel
                                </button>
                            </Link>

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
