import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
    Gift,
    ListChecks,
    MessageSquare,
    RotateCw,
    Sparkles,
    Star,
    Target,
    Trophy,
    Users,
} from "lucide-react";

const formatNumber = (value = 0) => new Intl.NumberFormat("en-US").format(value);

const formatDateTime = (dateTime) => {
    if (!dateTime) {
        return "No spin time";
    }

    return new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(dateTime.replace(" ", "T")));
};

const formatPrizeType = (type = "") =>
    type
        .split("_")
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ") || "Unknown";

const StatCard = ({ title, value, helper, icon: Icon, accent }) => (
    <div className="rounded-lg border border-white/10 bg-white/[0.07] p-4 shadow">
        <div className="flex items-start justify-between gap-4">
            <div>
                <p className="text-xs font-medium uppercase text-gray-300">
                    {title}
                </p>
                <p className="mt-2 text-2xl font-bold text-white">{value}</p>
            </div>
            <div
                className="grid h-10 w-10 place-items-center rounded-lg"
                style={{ backgroundColor: accent }}
            >
                <Icon className="h-5 w-5 text-white" />
            </div>
        </div>
        <p className="mt-3 text-xs text-gray-300">{helper}</p>
    </div>
);

const EmptyChart = () => (
    <div className="grid h-64 place-items-center rounded-lg border border-dashed border-white/15 text-sm text-gray-400">
        No spin wheel data yet
    </div>
);

const DailySpinChart = ({ rows }) => {
    const maxSpins = Math.max(...rows.map((row) => row.spin_count), 0);
    const maxRewards = Math.max(...rows.map((row) => row.reward_points), 0);
    const hasData = maxSpins > 0 || maxRewards > 0;

    if (!hasData) {
        return <EmptyChart />;
    }

    const width = 820;
    const height = 280;
    const padding = { top: 24, right: 28, bottom: 44, left: 48 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const barGap = 10;
    const barWidth = chartWidth / rows.length - barGap;
    const rewardPoints = rows.map((row, index) => {
        const x =
            padding.left +
            index * (chartWidth / rows.length) +
            chartWidth / rows.length / 2;
        const y =
            padding.top +
            chartHeight -
            (row.reward_points / Math.max(maxRewards, 1)) * chartHeight;

        return `${x},${y}`;
    });

    return (
        <div className="overflow-x-auto">
            <svg
                viewBox={`0 0 ${width} ${height}`}
                className="h-72 min-w-[760px] text-gray-300"
                role="img"
                aria-label="Daily spin count and reward points"
            >
                <line
                    x1={padding.left}
                    y1={padding.top + chartHeight}
                    x2={width - padding.right}
                    y2={padding.top + chartHeight}
                    stroke="rgba(255,255,255,0.18)"
                />
                {[0, 0.5, 1].map((tick) => (
                    <g key={tick}>
                        <line
                            x1={padding.left}
                            y1={padding.top + chartHeight - chartHeight * tick}
                            x2={width - padding.right}
                            y2={padding.top + chartHeight - chartHeight * tick}
                            stroke="rgba(255,255,255,0.08)"
                        />
                        <text
                            x={padding.left - 12}
                            y={padding.top + chartHeight - chartHeight * tick + 4}
                            textAnchor="end"
                            className="fill-gray-400 text-[11px]"
                        >
                            {formatNumber(Math.round(maxSpins * tick))}
                        </text>
                    </g>
                ))}
                {rows.map((row, index) => {
                    const x =
                        padding.left + index * (chartWidth / rows.length) + barGap / 2;
                    const barHeight =
                        (row.spin_count / Math.max(maxSpins, 1)) * chartHeight;
                    const y = padding.top + chartHeight - barHeight;

                    return (
                        <g key={row.date}>
                            <rect
                                x={x}
                                y={y}
                                width={Math.max(barWidth, 8)}
                                height={barHeight}
                                rx="4"
                                fill="#22c55e"
                            />
                            {row.super_prize_count > 0 ? (
                                <circle
                                    cx={x + Math.max(barWidth, 8) / 2}
                                    cy={Math.max(y - 8, 10)}
                                    r="4"
                                    fill="#facc15"
                                />
                            ) : null}
                            <text
                                x={x + Math.max(barWidth, 8) / 2}
                                y={height - 16}
                                textAnchor="middle"
                                className="fill-gray-400 text-[11px]"
                            >
                                {index % 2 === 0 ? row.label : ""}
                            </text>
                        </g>
                    );
                })}
                <polyline
                    points={rewardPoints.join(" ")}
                    fill="none"
                    stroke="#38bdf8"
                    strokeWidth="3"
                    strokeLinejoin="round"
                    strokeLinecap="round"
                />
                {rows.map((row, index) => {
                    const [x, y] = rewardPoints[index].split(",");
                    return (
                        <circle
                            key={`${row.date}-reward`}
                            cx={x}
                            cy={y}
                            r="4"
                            fill="#38bdf8"
                        >
                            <title>
                                {row.label}: {formatNumber(row.spin_count)} spins,{" "}
                                {formatNumber(row.reward_points)} points
                            </title>
                        </circle>
                    );
                })}
            </svg>
        </div>
    );
};

export default function Dashboard({ user, dashboard }) {
    const stats = dashboard?.stats ?? {};
    const dailySpins = dashboard?.daily_spins ?? [];
    const todayChances = dashboard?.today_chances ?? [];
    const recentSpinRecords = dashboard?.recent_spin_records ?? [];
    const recentFeedbacks = dashboard?.recent_feedbacks ?? [];
    const targetPercent =
        stats.super_prize_target > 0
            ? Math.min((stats.super_prize_progress / stats.super_prize_target) * 100, 100)
            : 0;

    return (
        <AuthenticatedLayout user={user}>
            <div className="space-y-8">
                <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h4 className="text-2xl font-bold text-white">Dashboard</h4>
                        <p className="mt-1 text-sm text-gray-300">
                            Spin wheel chances, reward records, and super prize progress.
                        </p>
                    </div>
                    <div className="rounded-lg border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-gray-300">
                        Last 14 days spin overview
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        title="Total members"
                        value={formatNumber(stats.total_members)}
                        helper="Registered members in the system"
                        icon={Users}
                        accent="#2563eb"
                    />
                    <StatCard
                        title="Today spins"
                        value={formatNumber(stats.today_spins)}
                        helper={`${formatNumber(stats.today_reward_points)} reward points today`}
                        icon={RotateCw}
                        accent="#16a34a"
                    />
                    <StatCard
                        title="Total spins"
                        value={formatNumber(stats.total_spins)}
                        helper="All spin records saved"
                        icon={ListChecks}
                        accent="#0891b2"
                    />
                    <StatCard
                        title="Remaining chances"
                        value={formatNumber(stats.today_remaining_chances)}
                        helper={`${formatNumber(stats.today_super_prize_remaining)} super prize chances left`}
                        icon={Gift}
                        accent="#d97706"
                    />
                    <StatCard
                        title="Super target"
                        value={formatNumber(stats.super_prize_target)}
                        helper={`${formatNumber(stats.spins_until_super_prize)} spins until next target`}
                        icon={Target}
                        accent="#db2777"
                    />
                    <StatCard
                        title="Feedbacks"
                        value={formatNumber(stats.total_feedbacks)}
                        helper="Submitted branch feedback"
                        icon={MessageSquare}
                        accent="#7c3aed"
                    />
                    <StatCard
                        title="Avg rating"
                        value={stats.average_feedback_rating || "N/A"}
                        helper="Average score from submitted feedback"
                        icon={Star}
                        accent="#eab308"
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
                    <section className="min-w-0">
                        <div className="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <h5 className="text-lg font-semibold text-white">
                                    Daily Spin Trend
                                </h5>
                                <p className="text-xs text-gray-400">
                                    Green bars show spins. Blue line shows reward points.
                                </p>
                            </div>
                        </div>
                        <DailySpinChart rows={dailySpins} />
                    </section>

                    <section className="min-w-0">
                        <div className="mb-4 flex items-center gap-2">
                            <Trophy className="h-5 w-5 text-yellow-300" />
                            <div>
                                <h5 className="text-lg font-semibold text-white">
                                    Super Prize Progress
                                </h5>
                                <p className="text-xs text-gray-400">
                                    Target comes from settings.super_prize_target.
                                </p>
                            </div>
                        </div>
                        <div className="rounded-lg border border-white/10 bg-white/[0.05] p-4">
                            <div className="flex items-end justify-between gap-4">
                                <div>
                                    <p className="text-xs uppercase text-gray-400">
                                        Current progress
                                    </p>
                                    <p className="mt-2 text-3xl font-bold text-white">
                                        {formatNumber(stats.super_prize_progress)}
                                        <span className="text-base font-medium text-gray-400">
                                            /{formatNumber(stats.super_prize_target)}
                                        </span>
                                    </p>
                                </div>
                                <Sparkles className="h-8 w-8 text-yellow-300" />
                            </div>
                            <div className="mt-5 h-3 overflow-hidden rounded bg-white/10">
                                <div
                                    className="h-full rounded bg-yellow-300"
                                    style={{ width: `${targetPercent}%` }}
                                />
                            </div>
                            <p className="mt-3 text-xs text-gray-300">
                                {formatNumber(stats.today_spins)} spins recorded today.
                            </p>
                        </div>
                    </section>
                </div>

                <section>
                    <div className="mb-4">
                        <h5 className="text-lg font-semibold text-white">
                            Today Chance Monitoring
                        </h5>
                        <p className="text-xs text-gray-400">
                            Daily chances decrease from spin_wheel_chances_daily as users spin.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-lg border border-white/10 bg-white/[0.04]">
                        <div className="max-h-[360px] overflow-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="sticky top-0 bg-slate-950/95 text-xs uppercase text-gray-400">
                                    <tr>
                                        <th className="px-4 py-3">Prize type</th>
                                        <th className="px-4 py-3 text-right">Points</th>
                                        <th className="px-4 py-3 text-right">Fixed value</th>
                                        <th className="px-4 py-3 text-right">Awarded</th>
                                        <th className="px-4 py-3 text-right">Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {todayChances.length > 0 ? (
                                        todayChances.map((chance) => (
                                            <tr
                                                key={chance.key}
                                                className="border-t border-white/5 hover:bg-white/[0.04]"
                                            >
                                                <td className="px-4 py-3 font-semibold text-white">
                                                    {formatPrizeType(chance.type)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-300">
                                                    {formatNumber(chance.points)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-300">
                                                    {formatNumber(chance.configured_times)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-300">
                                                    {formatNumber(chance.awarded_times)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-100">
                                                    {formatNumber(chance.remaining_times)}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-4 py-8 text-center text-gray-400"
                                            >
                                                No daily spin chances available today.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section>
                    <div className="mb-4">
                        <h5 className="text-lg font-semibold text-white">
                            Recent Spin Records
                        </h5>
                        <p className="text-xs text-gray-400">
                            Latest user spins saved in spin_records.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-lg border border-white/10 bg-white/[0.04]">
                        <div className="max-h-[360px] overflow-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="sticky top-0 bg-slate-950/95 text-xs uppercase text-gray-400">
                                    <tr>
                                        <th className="px-4 py-3">Member</th>
                                        <th className="px-4 py-3">Phone</th>
                                        <th className="px-4 py-3">Prize type</th>
                                        <th className="px-4 py-3 text-right">Reward</th>
                                        <th className="px-4 py-3 text-right">Remaining after</th>
                                        <th className="px-4 py-3">Spun at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentSpinRecords.length > 0 ? (
                                        recentSpinRecords.map((record) => (
                                            <tr
                                                key={record.id}
                                                className="border-t border-white/5 hover:bg-white/[0.04]"
                                            >
                                                <td className="px-4 py-3 font-semibold text-white">
                                                    {record.member?.name || "Unknown member"}
                                                </td>
                                                <td className="px-4 py-3 text-gray-300">
                                                    {record.member?.phone || "-"}
                                                </td>
                                                <td className="px-4 py-3 text-gray-300">
                                                    {formatPrizeType(record.type)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-100">
                                                    {formatNumber(record.reward_points)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-300">
                                                    {record.remaining_after_spin === null
                                                        ? "-"
                                                        : formatNumber(record.remaining_after_spin)}
                                                </td>
                                                <td className="px-4 py-3 text-gray-300">
                                                    {formatDateTime(record.spun_at)}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="6"
                                                className="px-4 py-8 text-center text-gray-400"
                                            >
                                                No spin records saved yet.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section>
                    <div className="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h5 className="text-lg font-semibold text-white">
                                Recent Feedbacks
                            </h5>
                            <p className="text-xs text-gray-400">
                                Latest member comments and support signals.
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        {recentFeedbacks.length > 0 ? (
                            recentFeedbacks.map((feedback) => (
                                <div
                                    key={feedback.id}
                                    className="rounded-lg border border-white/10 bg-white/[0.05] p-4"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-semibold text-white">
                                                {feedback.branch?.name || "Unknown branch"}
                                            </p>
                                            <p className="mt-1 text-xs text-gray-400">
                                                {feedback.user?.name || "Unknown member"}
                                            </p>
                                        </div>
                                        <span className="shrink-0 rounded bg-white/10 px-2 py-1 text-xs text-gray-200">
                                            {feedback.rating}/5
                                        </span>
                                    </div>
                                    <p className="mt-3 line-clamp-3 text-sm text-gray-300">
                                        {feedback.message}
                                    </p>
                                    <div className="mt-3 flex items-center justify-between text-xs text-gray-400">
                                        <span>{feedback.date || feedback.created_at}</span>
                                        <span>
                                            {feedback.rating}/5
                                            {feedback.images_count > 0
                                                ? `, ${feedback.images_count} images`
                                                : ""}
                                        </span>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="rounded-lg border border-dashed border-white/15 p-6 text-center text-sm text-gray-400 md:col-span-2 xl:col-span-3">
                                No feedback submitted yet.
                            </div>
                        )}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
