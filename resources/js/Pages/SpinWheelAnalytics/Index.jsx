import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import {
    CalendarRange,
    Gift,
    RotateCw,
    Sparkles,
    Trophy,
    UserRoundCheck,
} from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    ComposedChart,
    Label,
    Legend,
    Line,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from "recharts";

const PRIZE_COLORS = {
    mega_prize: "#f97316",
    super_prize: "#facc15",
    fix_prize: "#38bdf8",
    other: "#a78bfa",
    unknown: "#94a3b8",
};

const TOOLTIP_STYLE = {
    backgroundColor: "#0f172a",
    border: "1px solid rgba(255,255,255,0.14)",
    borderRadius: "10px",
    color: "#f8fafc",
};

const formatNumber = (value = 0, maximumFractionDigits = 0) =>
    new Intl.NumberFormat("en-US", { maximumFractionDigits }).format(value);

const formatPrizeType = (type = "") =>
    type
        .split("_")
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ") || "Unknown";

const formatDateTime = (dateTime) => {
    if (!dateTime) {
        return "-";
    }

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(dateTime.replace(" ", "T")));
};

const toInputDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const StatCard = ({ title, value, helper, icon: Icon, accent }) => (
    <div className="rounded-xl border border-white/10 bg-white/[0.06] p-4 shadow">
        <div className="flex items-start justify-between gap-4">
            <div className="min-w-0">
                <p className="text-xs font-medium uppercase tracking-wide text-gray-400">
                    {title}
                </p>
                <p className="mt-2 truncate text-2xl font-bold text-white">{value}</p>
            </div>
            <div
                className="grid h-10 w-10 shrink-0 place-items-center rounded-xl"
                style={{ backgroundColor: accent }}
            >
                <Icon className="h-5 w-5 text-white" aria-hidden="true" />
            </div>
        </div>
        <p className="mt-3 text-xs text-gray-400">{helper}</p>
    </div>
);

const EmptyChart = ({ message = "No spin data in this date range." }) => (
    <div className="grid h-72 place-items-center rounded-lg border border-dashed border-white/15 px-4 text-center text-sm text-gray-400">
        {message}
    </div>
);

const TargetWheel = ({ target }) => {
    const enabled = target.target > 0;
    const progress = enabled ? target.cycle_progress : 0;
    const remainder = enabled ? Math.max(target.target - progress, 0) : 1;
    const color = PRIZE_COLORS[target.type] ?? PRIZE_COLORS.unknown;
    const chartData = [
        { name: "Progress", value: progress },
        { name: "Remaining", value: remainder },
    ];

    return (
        <div className="rounded-xl border border-white/10 bg-white/[0.05] p-5">
            <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div className="h-44 w-full sm:w-44 sm:shrink-0">
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                            <Pie
                                data={chartData}
                                dataKey="value"
                                innerRadius={55}
                                outerRadius={72}
                                startAngle={90}
                                endAngle={-270}
                                stroke="none"
                            >
                                <Cell fill={color} />
                                <Cell fill="rgba(255,255,255,0.09)" />
                                <Label
                                    position="center"
                                    content={({ viewBox }) => {
                                        const { cx, cy } = viewBox;

                                        return (
                                            <g>
                                                <text
                                                    x={cx}
                                                    y={cy - 4}
                                                    textAnchor="middle"
                                                    className="fill-white text-xl font-bold"
                                                >
                                                    {enabled
                                                        ? `${formatNumber(target.progress_percent, 1)}%`
                                                        : "Off"}
                                                </text>
                                                <text
                                                    x={cx}
                                                    y={cy + 18}
                                                    textAnchor="middle"
                                                    className="fill-gray-400 text-[10px]"
                                                >
                                                    current cycle
                                                </text>
                                            </g>
                                        );
                                    }}
                                />
                            </Pie>
                        </PieChart>
                    </ResponsiveContainer>
                </div>

                <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2">
                        {target.type === "mega_prize" ? (
                            <Sparkles className="h-5 w-5" style={{ color }} />
                        ) : (
                            <Trophy className="h-5 w-5" style={{ color }} />
                        )}
                        <h3 className="text-lg font-semibold text-white">
                            {formatPrizeType(target.type)} target
                        </h3>
                    </div>
                    <p className="mt-2 text-sm text-gray-400">
                        {enabled
                            ? `${formatNumber(target.cycle_progress)} of ${formatNumber(target.target)} spins in the ${target.progress_date} cycle`
                            : "No valid target configured in Settings"}
                    </p>

                    <dl className="mt-4 grid grid-cols-3 gap-3 text-center">
                        <div className="rounded-lg bg-white/[0.05] px-2 py-3">
                            <dt className="text-[10px] uppercase text-gray-500">Awarded</dt>
                            <dd className="mt-1 font-semibold text-white">
                                {formatNumber(target.awarded_count)}
                            </dd>
                        </div>
                        <div className="rounded-lg bg-white/[0.05] px-2 py-3">
                            <dt className="text-[10px] uppercase text-gray-500">Crossings</dt>
                            <dd className="mt-1 font-semibold text-white">
                                {formatNumber(target.threshold_crossings)}
                            </dd>
                        </div>
                        <div className="rounded-lg bg-white/[0.05] px-2 py-3">
                            <dt className="text-[10px] uppercase text-gray-500">Next in</dt>
                            <dd className="mt-1 font-semibold text-white">
                                {target.spins_until_next === null
                                    ? "-"
                                    : formatNumber(target.spins_until_next)}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    );
};

const SpinTrendChart = ({ rows, granularity }) => {
    const hasData = rows.some(
        (row) => row.spin_count > 0 || row.reward_points > 0
    );

    if (!hasData) {
        return <EmptyChart />;
    }

    return (
        <div className="h-80 w-full">
            <ResponsiveContainer width="100%" height="100%">
                <ComposedChart data={rows} margin={{ top: 12, right: 12, left: 0, bottom: 4 }}>
                    <CartesianGrid stroke="rgba(255,255,255,0.07)" vertical={false} />
                    <XAxis
                        dataKey="label"
                        stroke="#94a3b8"
                        tickLine={false}
                        axisLine={{ stroke: "rgba(255,255,255,0.14)" }}
                        minTickGap={granularity === "hourly" ? 26 : 18}
                        fontSize={11}
                    />
                    <YAxis
                        yAxisId="spins"
                        stroke="#94a3b8"
                        tickLine={false}
                        axisLine={false}
                        allowDecimals={false}
                        width={42}
                        fontSize={11}
                    />
                    <YAxis
                        yAxisId="points"
                        orientation="right"
                        stroke="#38bdf8"
                        tickLine={false}
                        axisLine={false}
                        width={52}
                        fontSize={11}
                        tickFormatter={(value) => formatNumber(value)}
                    />
                    <Tooltip
                        contentStyle={TOOLTIP_STYLE}
                        labelStyle={{ color: "#f8fafc", fontWeight: 600 }}
                        formatter={(value, name) => [formatNumber(value), name]}
                    />
                    <Legend wrapperStyle={{ fontSize: 12, paddingTop: 10 }} />
                    <Bar
                        yAxisId="spins"
                        dataKey="spin_count"
                        name="Spins"
                        fill="#6366f1"
                        radius={[4, 4, 0, 0]}
                        maxBarSize={34}
                    />
                    <Line
                        yAxisId="points"
                        type="monotone"
                        dataKey="reward_points"
                        name="Reward points"
                        stroke="#38bdf8"
                        strokeWidth={2.5}
                        dot={false}
                        activeDot={{ r: 4 }}
                    />
                    <Line
                        yAxisId="spins"
                        type="linear"
                        dataKey="super_prize_count"
                        name="Super prizes"
                        stroke={PRIZE_COLORS.super_prize}
                        strokeWidth={2}
                        dot={{ r: 3 }}
                    />
                    <Line
                        yAxisId="spins"
                        type="linear"
                        dataKey="mega_prize_count"
                        name="Mega prizes"
                        stroke={PRIZE_COLORS.mega_prize}
                        strokeWidth={2}
                        strokeDasharray="5 4"
                        dot={{ r: 3 }}
                    />
                </ComposedChart>
            </ResponsiveContainer>
        </div>
    );
};

const PrizeDonut = ({ rows, totalSpins }) => {
    const chartRows = rows.filter((row) => row.spin_count > 0);

    if (chartRows.length === 0) {
        return <EmptyChart message="No awarded prize types in this date range." />;
    }

    return (
        <div>
            <div className="h-64 w-full">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={chartRows}
                            dataKey="spin_count"
                            nameKey="type"
                            innerRadius={65}
                            outerRadius={92}
                            paddingAngle={2}
                            stroke="none"
                        >
                            {chartRows.map((row) => (
                                <Cell
                                    key={row.type}
                                    fill={PRIZE_COLORS[row.type] ?? PRIZE_COLORS.unknown}
                                />
                            ))}
                            <Label
                                position="center"
                                content={({ viewBox }) => {
                                    const { cx, cy } = viewBox;

                                    return (
                                        <g>
                                            <text
                                                x={cx}
                                                y={cy - 5}
                                                textAnchor="middle"
                                                className="fill-white text-2xl font-bold"
                                            >
                                                {formatNumber(totalSpins)}
                                            </text>
                                            <text
                                                x={cx}
                                                y={cy + 17}
                                                textAnchor="middle"
                                                className="fill-gray-400 text-[11px]"
                                            >
                                                awarded spins
                                            </text>
                                        </g>
                                    );
                                }}
                            />
                        </Pie>
                        <Tooltip
                            contentStyle={TOOLTIP_STYLE}
                            formatter={(value, name, item) => [
                                `${formatNumber(value)} spins · ${formatNumber(item.payload.reward_points)} pts`,
                                formatPrizeType(name),
                            ]}
                        />
                    </PieChart>
                </ResponsiveContainer>
            </div>

            <div className="mt-2 grid gap-2 sm:grid-cols-2">
                {chartRows.map((row) => (
                    <div
                        key={row.type}
                        className="flex items-center justify-between gap-3 rounded-lg bg-white/[0.04] px-3 py-2 text-xs"
                    >
                        <span className="flex min-w-0 items-center gap-2 text-gray-300">
                            <span
                                className="h-2.5 w-2.5 shrink-0 rounded-full"
                                style={{
                                    backgroundColor:
                                        PRIZE_COLORS[row.type] ?? PRIZE_COLORS.unknown,
                                }}
                            />
                            <span className="truncate">{formatPrizeType(row.type)}</span>
                        </span>
                        <span className="font-semibold text-white">
                            {formatNumber(row.spin_count)}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
};

const InventoryChart = ({ rows }) => {
    if (rows.length === 0) {
        return <EmptyChart message="No daily wheel inventory in this date range." />;
    }

    const chartRows = rows.map((row) => ({
        ...row,
        label: `${formatPrizeType(row.type)} · ${formatNumber(row.points)} pts`,
    }));

    return (
        <div className="max-h-[420px] overflow-y-auto pr-1">
            <div style={{ height: Math.max(280, chartRows.length * 46) }}>
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart
                        data={chartRows}
                        layout="vertical"
                        margin={{ top: 8, right: 20, left: 12, bottom: 8 }}
                    >
                        <CartesianGrid stroke="rgba(255,255,255,0.07)" horizontal={false} />
                        <XAxis
                            type="number"
                            allowDecimals={false}
                            stroke="#94a3b8"
                            tickLine={false}
                            axisLine={{ stroke: "rgba(255,255,255,0.14)" }}
                            fontSize={11}
                        />
                        <YAxis
                            type="category"
                            dataKey="label"
                            width={128}
                            stroke="#cbd5e1"
                            tickLine={false}
                            axisLine={false}
                            fontSize={10}
                        />
                        <Tooltip
                            contentStyle={TOOLTIP_STYLE}
                            formatter={(value, name) => [formatNumber(value), name]}
                        />
                        <Legend wrapperStyle={{ fontSize: 12 }} />
                        <Bar
                            dataKey="awarded_times"
                            name="Awarded"
                            stackId="inventory"
                            fill="#6366f1"
                        />
                        <Bar
                            dataKey="remaining_times"
                            name="Remaining"
                            stackId="inventory"
                            fill="#334155"
                            radius={[0, 4, 4, 0]}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
};

export default function SpinWheelAnalytics({ user, analytics }) {
    const filters = analytics?.filters ?? {};
    const summary = analytics?.summary ?? {};
    const trend = analytics?.trend ?? [];
    const prizeDistribution = analytics?.prize_distribution ?? [];
    const inventory = analytics?.inventory ?? [];
    const targets = analytics?.targets ?? [];
    const recentRecords = analytics?.recent_records ?? [];
    const [startDate, setStartDate] = useState(filters.start_date ?? "");
    const [endDate, setEndDate] = useState(filters.end_date ?? "");

    useEffect(() => {
        setStartDate(filters.start_date ?? "");
        setEndDate(filters.end_date ?? "");
    }, [filters.start_date, filters.end_date]);

    const rangeLabel = useMemo(() => {
        if (filters.start_date === filters.end_date) {
            return filters.start_date;
        }

        return `${filters.start_date} to ${filters.end_date}`;
    }, [filters.end_date, filters.start_date]);

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(
            route("spin-wheel-analytics"),
            { start_date: startDate, end_date: endDate },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const applyPreset = (days) => {
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - (days - 1));
        const nextStart = toInputDate(start);
        const nextEnd = toInputDate(end);

        setStartDate(nextStart);
        setEndDate(nextEnd);
        router.get(
            route("spin-wheel-analytics"),
            { start_date: nextStart, end_date: nextEnd },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    return (
        <AuthenticatedLayout user={user}>
            <div className="space-y-7">
                <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <h2 className="text-2xl font-bold text-white">Spin Wheel Analytics</h2>
                        <p className="mt-1 text-sm text-gray-400">
                            Spin activity, prize awards, inventory, and target-cycle monitoring.
                        </p>
                    </div>

                    <form
                        onSubmit={applyFilters}
                        className="flex flex-wrap items-end gap-3 rounded-xl border border-white/10 bg-white/[0.05] p-3"
                    >
                        <label className="text-xs font-medium text-gray-300">
                            <span className="mb-1 block">From</span>
                            <input
                                type="date"
                                value={startDate}
                                max={endDate || undefined}
                                onChange={(event) => setStartDate(event.target.value)}
                                className="rounded-lg border-white/10 bg-slate-950/70 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400"
                                required
                            />
                        </label>
                        <label className="text-xs font-medium text-gray-300">
                            <span className="mb-1 block">To</span>
                            <input
                                type="date"
                                value={endDate}
                                min={startDate || undefined}
                                onChange={(event) => setEndDate(event.target.value)}
                                className="rounded-lg border-white/10 bg-slate-950/70 text-sm text-white focus:border-indigo-400 focus:ring-indigo-400"
                                required
                            />
                        </label>
                        <button
                            type="submit"
                            className="inline-flex h-[38px] items-center gap-2 rounded-lg bg-indigo-500 px-4 text-sm font-semibold text-white hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        >
                            <CalendarRange className="h-4 w-4" />
                            Apply
                        </button>
                        <div className="flex gap-1">
                            {[1, 7, 30].map((days) => (
                                <button
                                    key={days}
                                    type="button"
                                    onClick={() => applyPreset(days)}
                                    className="h-[38px] rounded-lg bg-white/[0.07] px-3 text-xs text-gray-300 hover:bg-white/[0.12]"
                                >
                                    {days === 1 ? "Today" : `${days}D`}
                                </button>
                            ))}
                        </div>
                    </form>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-400">
                    <span>Reporting period: {rangeLabel}</span>
                    <span>
                        Trend granularity: {analytics?.trend_granularity === "hourly" ? "Hourly" : "Daily"}
                    </span>
                </div>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        title="Total spins"
                        value={formatNumber(summary.total_spins)}
                        helper={`${formatNumber(summary.super_prize_count)} super · ${formatNumber(summary.mega_prize_count)} mega`}
                        icon={RotateCw}
                        accent="#4f46e5"
                    />
                    <StatCard
                        title="Unique spinners"
                        value={formatNumber(summary.unique_spinners)}
                        helper="Distinct members who spun in the period"
                        icon={UserRoundCheck}
                        accent="#0f766e"
                    />
                    <StatCard
                        title="Reward points"
                        value={formatNumber(summary.reward_points)}
                        helper="Total points awarded from spin records"
                        icon={Gift}
                        accent="#0369a1"
                    />
                    <StatCard
                        title="Average reward"
                        value={formatNumber(summary.average_reward_points, 1)}
                        helper="Reward points per recorded spin"
                        icon={Sparkles}
                        accent="#a16207"
                    />
                </section>

                <section className="grid gap-4 xl:grid-cols-2">
                    {targets.map((target) => (
                        <TargetWheel key={target.type} target={target} />
                    ))}
                </section>

                <section className="grid gap-4 xl:grid-cols-3">
                    <div className="rounded-xl border border-white/10 bg-white/[0.04] p-5 xl:col-span-2">
                        <div className="mb-4">
                            <h3 className="text-lg font-semibold text-white">Spin trend</h3>
                            <p className="mt-1 text-xs text-gray-400">
                                Spins and reward points with super/mega prize events over the selected period.
                            </p>
                        </div>
                        <SpinTrendChart
                            rows={trend}
                            granularity={analytics?.trend_granularity}
                        />
                    </div>

                    <div className="rounded-xl border border-white/10 bg-white/[0.04] p-5">
                        <div className="mb-2">
                            <h3 className="text-lg font-semibold text-white">Prize mix</h3>
                            <p className="mt-1 text-xs text-gray-400">
                                Recorded spins grouped by awarded prize type.
                            </p>
                        </div>
                        <PrizeDonut
                            rows={prizeDistribution}
                            totalSpins={summary.total_spins}
                        />
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-5">
                    <div className="rounded-xl border border-white/10 bg-white/[0.04] p-5 xl:col-span-3">
                        <div className="mb-4">
                            <h3 className="text-lg font-semibold text-white">Prize inventory usage</h3>
                            <p className="mt-1 text-xs text-gray-400">
                                Awarded versus remaining daily chances by prize type and points.
                            </p>
                        </div>
                        <InventoryChart rows={inventory} />
                    </div>

                    <div className="overflow-hidden rounded-xl border border-white/10 bg-white/[0.04] xl:col-span-2">
                        <div className="p-5 pb-3">
                            <h3 className="text-lg font-semibold text-white">Inventory detail</h3>
                            <p className="mt-1 text-xs text-gray-400">
                                Available is calculated as awarded plus current remaining.
                            </p>
                        </div>
                        <div className="max-h-[420px] overflow-auto">
                            <table className="w-full min-w-[560px] text-left text-xs">
                                <thead className="sticky top-0 bg-slate-950/95 uppercase text-gray-400">
                                    <tr>
                                        <th className="px-4 py-3">Prize</th>
                                        <th className="px-4 py-3 text-right">Available</th>
                                        <th className="px-4 py-3 text-right">Awarded</th>
                                        <th className="px-4 py-3 text-right">Remaining</th>
                                        <th className="px-4 py-3 text-right">Used</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {inventory.length > 0 ? (
                                        inventory.map((row) => (
                                            <tr
                                                key={row.key}
                                                className="border-t border-white/5 text-gray-300 hover:bg-white/[0.03]"
                                            >
                                                <td className="px-4 py-3">
                                                    <span className="font-medium text-white">
                                                        {formatPrizeType(row.type)}
                                                    </span>
                                                    <span className="ml-1 text-gray-500">
                                                        · {formatNumber(row.points)} pts
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    {formatNumber(row.available_times)}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    {formatNumber(row.awarded_times)}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    {formatNumber(row.remaining_times)}
                                                </td>
                                                <td className="px-4 py-3 text-right text-white">
                                                    {formatNumber(row.utilization_percent, 1)}%
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="5" className="px-4 py-8 text-center text-gray-400">
                                                No daily chance rows in this range.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section className="overflow-hidden rounded-xl border border-white/10 bg-white/[0.04]">
                    <div className="p-5 pb-3">
                        <h3 className="text-lg font-semibold text-white">Recent spins in range</h3>
                        <p className="mt-1 text-xs text-gray-400">
                            Latest 15 spin records matching the active date filter.
                        </p>
                    </div>
                    <div className="max-h-[430px] overflow-auto">
                        <table className="w-full min-w-[820px] text-left text-sm">
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
                                {recentRecords.length > 0 ? (
                                    recentRecords.map((record) => (
                                        <tr
                                            key={record.id}
                                            className="border-t border-white/5 text-gray-300 hover:bg-white/[0.03]"
                                        >
                                            <td className="px-4 py-3 font-medium text-white">
                                                {record.member?.name || "Unknown member"}
                                            </td>
                                            <td className="px-4 py-3">
                                                {record.member?.phone || "-"}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span
                                                    className="inline-flex rounded-full px-2.5 py-1 text-xs font-medium text-slate-950"
                                                    style={{
                                                        backgroundColor:
                                                            PRIZE_COLORS[record.type] ?? PRIZE_COLORS.unknown,
                                                    }}
                                                >
                                                    {formatPrizeType(record.type)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right text-white">
                                                {formatNumber(record.reward_points)}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                {record.remaining_after_spin === null
                                                    ? "-"
                                                    : formatNumber(record.remaining_after_spin)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {formatDateTime(record.spun_at)}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-4 py-10 text-center text-gray-400">
                                            No spin records in this date range.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
