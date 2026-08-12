import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm, Link, router } from "@inertiajs/react";
import { motion } from "framer-motion";

export default function CreateFAQ({ user }) {
    const { data, setData, post, processing, errors, setError, clearErrors } =
        useForm({
            question: "",
            answer: "",
            is_active: true,
        });

    const submit = (e) => {
        e.preventDefault();

        // 🔴 Frontend validation
        let hasError = false;

        clearErrors();

        if (!data.question.trim()) {
            setError("question", "Question is required");
            hasError = true;
        }

        if (!data.answer.trim()) {
            setError("answer", "Answer is required");
            hasError = true;
        }

        if (hasError) return;

        post("/faqs");
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="max-w-3xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-4">
                    <h4 className="text-xl font-bold text-white">Create FAQ</h4>
                </div>

                {/* Back */}
                <button className="bg-white/10 flex items-center px-4 py-2 rounded-2xl mb-4 hover:bg-white/15" onClick={() => router.get('/faqs')}>
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
                            d="M15 9l-7 7 7-7-7 7 7 7 7 7-7z"
                        />
                    </svg>
                    <span className="ml-2 text-white text-lg">Back</span>
                </button>

                {/* Form Card */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-white/10 rounded-2xl shadow-lg p-6 backdrop-blur-md"
                >
                    <form onSubmit={submit} className="space-y-5">
                        {/* Question */}
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Question <span className="text-red-400">*</span>
                            </label>

                            <input
                                type="text"
                                value={data.question}
                                onChange={(e) =>
                                    setData("question", e.target.value)
                                }
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.question
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            />

                            {errors.question && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.question}
                                </p>
                            )}
                        </div>

                        {/* Answer */}
                        <div>
                            <label className="block text-lg mb-2 text-white">
                                Answer <span className="text-red-400">*</span>
                            </label>

                            <textarea
                                rows={5}
                                value={data.answer}
                                onChange={(e) =>
                                    setData("answer", e.target.value)
                                }
                                className={`w-full px-3 py-2 rounded-lg bg-white/10 text-lg text-white focus:outline-none focus:ring-2
                                    ${
                                        errors.answer
                                            ? "ring-red-500"
                                            : "focus:ring-indigo-400"
                                    }`}
                            />

                            {errors.answer && (
                                <p className="text-red-400 text-xs mt-1">
                                    {errors.answer}
                                </p>
                            )}
                        </div>

                        {/* Active Toggle */}
                        <div className="flex items-center gap-3">
                            <label className="relative inline-block w-14 h-7 cursor-pointer">
                                <input
                                    type="checkbox"
                                    className="peer sr-only"
                                    checked={data.is_active}
                                    onChange={(e) =>
                                        setData("is_active", e.target.checked)
                                    }
                                />

                                <span className="absolute inset-0 bg-gray-300 rounded-lg transition peer-checked:bg-green-500"></span>
                                <span className="absolute left-1 bottom-1 w-5 h-5 bg-white rounded-md transition peer-checked:translate-x-6"></span>
                            </label>
                        </div>

                        {/* Submit */}
                        <div className="flex justify-end gap-3">
                            <Link href={route("faqs")}>
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
                                Create
                            </button>
                        </div>
                    </form>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
