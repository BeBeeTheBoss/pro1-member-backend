import { useState } from "react";
import { motion } from "framer-motion";
import BackgroundImage from "../../images/23257009490831583-30135024428938922.jpg"; // import your image
import { router } from "@inertiajs/react";

export default function LoginPage() {
    const [empCode, setEmpCode] = useState("");
    const [password, setPassword] = useState("");
    const [loading,setLoading] = useState(false);

    const handleLogin = () => {

        console.log(empCode);
        console.log(password);

        setLoading(true);

        router.post(
            "/login",
            {
                empCode: empCode,
                password: password,
            },
            {
                onError: (errors) => {
                    setLoading(false);
                    console.log(errors);
                },
                onSuccess: (response) => {
                    setLoading(false);
                    console.log(response);
                },
            }
        );
    };

    return (
        <div
            className="min-h-screen flex items-center justify-center p-4 bg-cover bg-center"
            style={{ backgroundImage: `url(${BackgroundImage})` }}
        >
            <motion.div
                initial={{ opacity: 0, y: 40 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
                className="w-full max-w-md"
            >
                <div className="shadow-xl rounded-2xl border border-white/20 bg-white/10 backdrop-blur-md px-4 py-14">
                    {/* Your content */}
                    <div>
                        <div className="text-center text-2xl font-semibold text-white">
                            Welcome Back
                        </div>
                    </div>
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-white">
                                Emp code
                            </label>
                            <input
                                type="email"
                                value={empCode}
                                onChange={(e) => setEmpCode(e.target.value)}
                                placeholder="000-000000"
                                className="rounded-xl outline-none text-black w-100"
                                style={{
                                    backgroundColor: "white",
                                    padding: "10px",
                                }}
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-white">
                                Password
                            </label>
                            <input
                                type="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                placeholder="password"
                                className="rounded-xl text-black w-100"
                                style={{
                                    backgroundColor: "white",
                                    padding: "10px",
                                }}
                            />
                        </div>
                        <button
                            onClick={handleLogin}
                            className="w-full rounded-xl text-white bg-indigo-600"
                            style={{ padding: "10px 0px", marginTop: 30 }}
                        >
                            {loading ? "Loading..." : "Login"}
                        </button>
                    </div>
                </div>
            </motion.div>
        </div>
    );
}
