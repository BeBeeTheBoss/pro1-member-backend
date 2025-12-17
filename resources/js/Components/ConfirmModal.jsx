import { motion, AnimatePresence } from "framer-motion";

export default function ConfirmModal({
    open,
    title = "Confirm Action",
    message = "Are you sure?",
    confirmText = "Confirm",
    cancelText = "Cancel",
    onConfirm,
    onCancel,
}) {
    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
                >
                    <motion.div
                        initial={{ scale: 0.85, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        exit={{ scale: 0.85, opacity: 0 }}
                        transition={{ duration: 0.2 }}
                        className="bg-dark w-[380px] rounded-2xl p-6 shadow-xl border border-white/10"
                    >
                        <h3 className="text-lg font-semibold text-white mb-2">
                            {title}
                        </h3>

                        <p className="text-sm text-gray-300 mb-6">
                            {message}
                        </p>

                        <div className="flex justify-end gap-3">
                            <button
                                onClick={onCancel}
                                className="px-4 py-2 rounded-lg bg-white/10 text-gray-200 hover:bg-white/20"
                            >
                                {cancelText}
                            </button>

                            <button
                                onClick={onConfirm}
                                className="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600"
                            >
                                {confirmText}
                            </button>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
