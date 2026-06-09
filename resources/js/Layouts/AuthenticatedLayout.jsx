import React, { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import BackgroundImage from "../images/23257009490831583-30135024428938922.jpg";
import { router, usePage } from "@inertiajs/react";

// ModernUI6.jsx
// Futuristic glassmorphic dashboard with animated sidebar, floating widgets, and neon accents.

export default function ModernUI6({
    user = null,
    modelOpen = false,
    setModelOpen = () => {},
    onLogout = () => {
        router.post("/logout");
    },
    children,
}) {
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [profileOpen, setProfileOpen] = useState(false);
    const [currentPage, setCurrentPage] = useState("dashboard");
    const lockedActions = React.useRef(new Set());
    const lockedForms = React.useRef(new Set());
    const unlockTimer = React.useRef(null);

    const { url } = usePage();

    useEffect(() => {
        console.log(url.split("/")[1]);

        setCurrentPage(url.split("/")[1]);
    }, [url]);

    useEffect(() => {
        const actionLabels = ["create", "update", "delete", "submit", "logout"];
        let visitActive = false;

        const unlockActions = () => {
            lockedActions.current.forEach((element) => {
                element.disabled = false;
                element.removeAttribute("aria-busy");
                element.classList.remove("pointer-events-none", "opacity-50");
            });
            lockedForms.current.forEach((form) => {
                form.removeAttribute("data-submitting");
            });
            lockedActions.current.clear();
            lockedForms.current.clear();

            if (unlockTimer.current) {
                clearTimeout(unlockTimer.current);
                unlockTimer.current = null;
            }
        };

        const scheduleFallbackUnlock = () => {
            if (unlockTimer.current) {
                clearTimeout(unlockTimer.current);
            }

            unlockTimer.current = setTimeout(() => {
                if (!visitActive) {
                    unlockActions();
                }
            }, 1200);
        };

        const lockElement = (element) => {
            if (!element || lockedActions.current.has(element)) {
                return false;
            }

            lockedActions.current.add(element);
            element.disabled = true;
            element.setAttribute("aria-busy", "true");
            element.classList.add("pointer-events-none", "opacity-50");
            scheduleFallbackUnlock();

            return true;
        };

        const isActionButton = (button, includeSubmit = true) => {
            const type = (button.getAttribute("type") || "").toLowerCase();
            const label = (button.textContent || "").trim().toLowerCase();

            return (
                (includeSubmit && type === "submit") ||
                actionLabels.some((action) => label.includes(action))
            );
        };

        const handleClick = (event) => {
            const button = event.target.closest("button");

            if (
                !button ||
                (button.getAttribute("type") || "").toLowerCase() === "submit" ||
                !isActionButton(button, false)
            ) {
                return;
            }

            if (lockedActions.current.has(button)) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            lockElement(button);
        };

        const handleSubmit = (event) => {
            const form = event.target;
            const submitter = event.submitter;

            if (
                form?.getAttribute("data-submitting") === "true" ||
                (submitter && lockedActions.current.has(submitter))
            ) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            if (form) {
                form.setAttribute("data-submitting", "true");
                lockedForms.current.add(form);
                scheduleFallbackUnlock();
            }

            lockElement(submitter);
        };

        document.addEventListener("click", handleClick, true);
        document.addEventListener("submit", handleSubmit, true);
        const removeStartListener = router.on("start", () => {
            visitActive = true;

            if (unlockTimer.current) {
                clearTimeout(unlockTimer.current);
                unlockTimer.current = null;
            }
        });
        const removeFinishListener = router.on("finish", () => {
            visitActive = false;
            unlockActions();
        });

        return () => {
            document.removeEventListener("click", handleClick, true);
            document.removeEventListener("submit", handleSubmit, true);
            removeStartListener();
            removeFinishListener();
            unlockActions();
        };
    }, []);

    const menuItems = [
        { label: "Dashboard" },
        { label: "Members" },
        { label: "Branches" },
        { label: "Notifications" },
        { label: "Feedbacks" },
        // { label: "Policies" },
        { label: "FAQs" },
        { label: "Popups" },
        { label: "Privilege-Categories", path: "privilege-categories" },
        { label: "Privileges" },
        { label: "Event-Platforms", path: "event-platforms" },
        { label: "Events" },
        { label: "Games-Events", path: "games-events" },
        { label: "Spin-Wheel-Chances", path: "spin-wheel-chances" },
    ];

    const handleDelete = (id) => {
        router.delete("/members/" + id);
    };

    return (
        <div
            className="h-screen p-6 flex gap-6 text-white bg-dark bg-cover bg-center overflow-hidden"
            // style={{ backgroundImage: `url(${BackgroundImage})` }}
        >

            <motion.aside
                initial={{ x: -50, opacity: 0 }}
                animate={{ x: 0, opacity: 1 }}
                transition={{ type: "spring", stiffness: 100, damping: 15 }}
                className="h-full shrink-0 bg-white/5 backdrop-blur-2xl border border-white/10 shadow-lg rounded-3xl py-5 px-4 flex flex-col overflow-hidden"
                style={{ width: sidebarOpen ? 280 : 80 }}
            >
                <nav className="flex-1 min-h-0 flex flex-col gap-2 overflow-y-auto overflow-x-hidden pr-1">
                    {menuItems.map((item, i) => (
                        <motion.div
                            key={item.label}
                            onClick={() => {
                                const path = item.path ?? item.label.toLowerCase();
                                router.get("/" + path);
                            }}
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ delay: i * 0.08 }}
                            // whileHover={{
                            //     scale: 1.05,
                            //     backgroundColor: "#ffffff1a",
                            // }}
                            className={
                                currentPage === item.label.toLowerCase()
                                    ? "flex items-center gap-3 cursor-pointer p-3 rounded-2xl bg-white/10"
                                    : "flex items-center gap-3 cursor-pointer p-3 rounded-2xl"
                            }
                        >
                            <span className="text-xl">{item.icon}</span>
                            {sidebarOpen && (
                                <span className="text-sm font-medium">
                                    {item.label}
                                </span>
                            )}
                        </motion.div>
                    ))}
                </nav>

                {user && sidebarOpen && (
                    <div className="mt-auto pt-6 border-t border-white/20">
                        <div className="flex items-center gap-3">
                            <div className="h-10 w-10 rounded-full bg-indigo-500 text-white flex items-center justify-center font-semibold shadow-lg">
                                {user.name.charAt(0)}
                            </div>
                            <div>
                                <div className="text-sm font-semibold">
                                    {user.name}
                                </div>
                                <div className="text-xs text-gray-300">
                                    {user.emp_code}
                                </div>
                            </div>

                            <button
                                onClick={onLogout}
                                className="px-3 py-1 rounded-xl bg-red-500 text-white text-xs shadow hover:bg-red-600"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                )}
            </motion.aside>

            <div className="min-w-0 flex-1 flex flex-col gap-6">

                <motion.header
                    initial={{ opacity: 0, y: -25 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ type: "spring", stiffness: 120, damping: 16 }}
                    className="rounded-3xl bg-white/5 backdrop-blur-2xl border border-white/10 shadow flex justify-between items-center"
                    style={{ padding: "30px 30px" }}
                >
                    <div>
                        <h1 className="text-xl font-bold">
                            Member Control Dashboard
                        </h1>
                        {user && (
                            <div className="text-sm text-gray-300">
                                Welcome back, {user.name}
                            </div>
                        )}
                    </div>
                </motion.header>

                <motion.main
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ type: "spring", stiffness: 120, damping: 16 }}
                    className="min-h-0 flex-1 rounded-3xl p-10 bg-white/5 backdrop-blur-2xl border border-white/10 shadow-lg"
                    style={{
                        overflow: "auto",
                    }}
                >
                    {children}
                </motion.main>

            </div>
        </div>
    );
}
