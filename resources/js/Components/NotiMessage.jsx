import { AnimatePresence, motion } from "framer-motion";

export default function NotiMessage({
    open,
    icon,
    header,
    message
}) {
    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    initial={{ opacity: 0, y: 150 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 150 }}
                    transition={{ duration: 0.2 }}
                    className="fixed bottom-5 right-5 rounded-2xl ps-3 pe-5 py-4 backdrop-blur-xl shadow-lg text-white z-[9999]"
                    style={{ border: "1px solid #ffffff3f" }}
                >
                    <div className="flex items-center pe-10 me-10">
                        <div className="me-3">
                            <img src={icon} style={{ width: 60 }} />
                        </div>
                        <div>
                            <h5>{header}</h5>
                            <div className="text-sm font-medium">
                                {message}
                            </div>
                        </div>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
