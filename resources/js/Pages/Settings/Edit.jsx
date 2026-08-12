import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";

export default function EditSetting({ setting, user }) {
    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            original_attribute: setting.attribute,
            attribute: setting.attribute,
            value: setting.value ?? "",
        });

    const submit = (e) => {
        e.preventDefault();

        let hasError = false;

        clearErrors();

        if (!data.attribute.trim()) {
            setError("attribute", "Attribute is required");
            hasError = true;
        }

        if (hasError) return;

        post("/settings/update");
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">
                        Edit Setting
                    </h4>
                </div>

                <button
                    className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15"
                    onClick={() => router.get("/settings")}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5 text-white mb-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    <span className="ml-2 text-white text-lg">Back</span>
                </button>

                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Attribute <span className="text-red-400">*</span>
                            </label>

                            <input
                                type="text"
                                value={data.attribute}
                                onChange={(e) =>
                                    setData("attribute", e.target.value)
                                }
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 ${
                                    errors.attribute
                                        ? "ring-red-500"
                                        : "focus:ring-indigo-400"
                                }`}
                            />

                            {errors.attribute && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.attribute}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Value
                            </label>

                            <textarea
                                rows={6}
                                value={data.value}
                                onChange={(e) => setData("value", e.target.value)}
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2 ${
                                    errors.value
                                        ? "ring-red-500"
                                        : "focus:ring-indigo-400"
                                }`}
                            />

                            {errors.value && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.value}
                                </p>
                            )}
                        </div>

                        <div className="flex justify-end gap-3">
                            <Link href={route("settings")}>
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
