import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Activity, Clock, MessageSquare, Star, Timer, UserCheck, Users } from "lucide-react";

const formatDuration = (seconds = 0) => {
    const totalSeconds = Number(seconds) || 0;
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }

    return `${minutes}m`;
};

const formatNumber = (value = 0) => new Intl.NumberFormat("en-US").format(value);

const formatDateTime = (dateTime) => {
    if (!dateTime) {
        return "No activity";
    }

    return new Intl.DateTimeFormat("en-US", {
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(dateTime.replace(" ", "T")));
};

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
        No usage data yet
    </div>
);

const DailyUsageChart = ({ rows }) => {
    const maxSeconds = Math.max(...rows.map((row) => row.total_seconds), 0);
    const maxSessions = Math.max(...rows.map((row) => row.session_count), 0);
    const hasData = maxSeconds > 0 || maxSessions > 0;

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
    const points = rows.map((row, index) => {
        const x =
            padding.left +
            index * (chartWidth / rows.length) +
            chartWidth / rows.length / 2;
        const y =
            padding.top +
            chartHeight -
            (row.session_count / Math.max(maxSessions, 1)) * chartHeight;

        return `${x},${y}`;
    });

    return (
        <div className="overflow-x-auto">
            <svg
                viewBox={`0 0 ${width} ${height}`}
                className="h-72 min-w-[760px] text-gray-300"
                role="img"
                aria-label="Daily member app usage and session count"
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
                            {formatDuration(maxSeconds * tick)}
                        </text>
                    </g>
                ))}
                {rows.map((row, index) => {
                    const x =
                        padding.left + index * (chartWidth / rows.length) + barGap / 2;
                    const barHeight =
                        (row.total_seconds / Math.max(maxSeconds, 1)) *
                        chartHeight;
                    const y = padding.top + chartHeight - barHeight;

                    return (
                        <g key={row.date}>
                            <rect
                                x={x}
                                y={y}
                                width={Math.max(barWidth, 8)}
                                height={barHeight}
                                rx="4"
                                fill="#14b8a6"
                            />
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
                    points={points.join(" ")}
                    fill="none"
                    stroke="#f59e0b"
                    strokeWidth="3"
                    strokeLinejoin="round"
                    strokeLinecap="round"
                />
                {rows.map((row, index) => {
                    const [x, y] = points[index].split(",");
                    return (
                        <circle
                            key={`${row.date}-point`}
                            cx={x}
                            cy={y}
                            r="4"
                            fill="#f59e0b"
                        >
                            <title>
                                {row.label}: {formatDuration(row.total_seconds)},{" "}
                                {row.session_count} sessions
                            </title>
                        </circle>
                    );
                })}
            </svg>
        </div>
    );
};

const TopMembersChart = ({ members }) => {
    const maxUsage = Math.max(
        ...members.map((member) => member.total_usage_time_in_seconds),
        0
    );

    if (maxUsage === 0) {
        return <EmptyChart />;
    }

    return (
        <div className="space-y-4">
            {members.map((member, index) => {
                const percent =
                    (member.total_usage_time_in_seconds / Math.max(maxUsage, 1)) *
                    100;

                return (
                    <div key={member.id} className="grid gap-2">
                        <div className="flex items-center justify-between gap-4 text-sm">
                            <div className="min-w-0">
                                <p className="truncate font-semibold text-white">
                                    {index + 1}. {member.name}
                                </p>
                                <p className="text-xs text-gray-400">{member.phone}</p>
                            </div>
                            <span className="shrink-0 text-gray-200">
                                {formatDuration(member.total_usage_time_in_seconds)}
                            </span>
                        </div>
                        <div className="h-3 overflow-hidden rounded bg-white/10">
                            <div
                                className="h-full rounded bg-cyan-400"
                                style={{ width: `${Math.max(percent, 4)}%` }}
                            />
                        </div>
                    </div>
                );
            })}
        </div>
    );
};

export default function Dashboard({ user, dashboard }) {
    const stats = dashboard?.stats ?? {};
    const dailyUsage = dashboard?.daily_usage ?? [];
    const topMembers = dashboard?.top_members ?? [];
    const recentMembers = dashboard?.recent_members ?? [];
    const recentFeedbacks = dashboard?.recent_feedbacks ?? [];

    return (
        <AuthenticatedLayout user={user}>
            <div className="space-y-8">
                <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h4 className="text-2xl font-bold text-white">Dashboard</h4>
                        <p className="mt-1 text-sm text-gray-300">
                            Member totals, app usage time, and session activity.
                        </p>
                    </div>
                    <div className="rounded-lg border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-gray-300">
                        Last 14 days usage overview
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        title="Total members"
                        value={formatNumber(stats.total_members)}
                        helper={`${formatNumber(stats.recent_active_members)} active in the last 7 days`}
                        icon={Users}
                        accent="#2563eb"
                    />
                    <StatCard
                        title="Active sessions"
                        value={formatNumber(stats.active_sessions)}
                        helper="Sessions currently without an inactive time"
                        icon={Activity}
                        accent="#16a34a"
                    />
                    <StatCard
                        title="Today sessions"
                        value={formatNumber(stats.today_sessions)}
                        helper={`${formatDuration(stats.today_usage_seconds)} tracked today`}
                        icon={Timer}
                        accent="#d97706"
                    />
                    <StatCard
                        title="Total usage"
                        value={formatDuration(stats.total_usage_seconds)}
                        helper="Accumulated from member usage totals"
                        icon={Clock}
                        accent="#0891b2"
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

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.6fr)]">
                    <section className="min-w-0">
                        <div className="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <h5 className="text-lg font-semibold text-white">
                                    Daily Usage Trend
                                </h5>
                                <p className="text-xs text-gray-400">
                                    Teal bars show usage time. Amber line shows sessions.
                                </p>
                            </div>
                        </div>
                        <DailyUsageChart rows={dailyUsage} />
                    </section>

                    <section className="min-w-0">
                        <div className="mb-4 flex items-center gap-2">
                            <UserCheck className="h-5 w-5 text-cyan-300" />
                            <div>
                                <h5 className="text-lg font-semibold text-white">
                                    Top Usage Members
                                </h5>
                                <p className="text-xs text-gray-400">
                                    Ranked by total usage time.
                                </p>
                            </div>
                        </div>
                        <TopMembersChart members={topMembers} />
                    </section>
                </div>

                <section>
                    <div className="mb-4">
                        <h5 className="text-lg font-semibold text-white">
                            Member Monitoring
                        </h5>
                        <p className="text-xs text-gray-400">
                            Latest member activity with last logged in time and total usage.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-lg border border-white/10 bg-white/[0.04]">
                        <div className="max-h-[360px] overflow-auto">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="sticky top-0 bg-slate-950/95 text-xs uppercase text-gray-400">
                                    <tr>
                                        <th className="px-4 py-3">Member</th>
                                        <th className="px-4 py-3">Phone</th>
                                        <th className="px-4 py-3">Last logged in</th>
                                        <th className="px-4 py-3 text-right">
                                            Total usage
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentMembers.length > 0 ? (
                                        recentMembers.map((member) => (
                                            <tr
                                                key={member.id}
                                                className="border-t border-white/5 hover:bg-white/[0.04]"
                                            >
                                                <td className="px-4 py-3 font-semibold text-white">
                                                    {member.name}
                                                </td>
                                                <td className="px-4 py-3 text-gray-300">
                                                    {member.phone}
                                                </td>
                                                <td className="px-4 py-3 text-gray-300">
                                                    {formatDateTime(
                                                        member.last_logged_in_time
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-right text-gray-100">
                                                    {formatDuration(
                                                        member.total_usage_time_in_seconds
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="4"
                                                className="px-4 py-8 text-center text-gray-400"
                                            >
                                                No member activity recorded yet.
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
