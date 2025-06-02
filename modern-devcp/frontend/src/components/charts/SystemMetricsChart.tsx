import React from 'react'
import {
  ResponsiveContainer,
  AreaChart,
  Area,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
} from 'recharts'

interface SystemMetricsChartProps {
  metrics: {
    cpu: number
    memory: number
    disk: number
    network: number
  }
}

const SystemMetricsChart: React.FC<SystemMetricsChartProps> = ({ metrics }) => {
  // Generate mock time series data
  const data = Array.from({ length: 24 }, (_, i) => {
    const hour = i
    const baseTime = new Date()
    baseTime.setHours(hour, 0, 0, 0)
    
    return {
      time: baseTime.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
      cpu: Math.max(0, metrics.cpu + (Math.random() - 0.5) * 20),
      memory: Math.max(0, metrics.memory + (Math.random() - 0.5) * 15),
      disk: Math.max(0, metrics.disk + (Math.random() - 0.5) * 10),
      network: Math.max(0, metrics.network + (Math.random() - 0.5) * 30),
    }
  })

  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-white dark:bg-gray-800 p-3 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
          <p className="text-sm font-medium text-gray-900 dark:text-white mb-2">
            {label}
          </p>
          {payload.map((entry: any, index: number) => (
            <p
              key={index}
              className="text-sm"
              style={{ color: entry.color }}
            >
              {entry.name}: {entry.value.toFixed(1)}%
            </p>
          ))}
        </div>
      )
    }
    return null
  }

  return (
    <div className="h-64">
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
          <defs>
            <linearGradient id="colorCpu" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.8} />
              <stop offset="95%" stopColor="#3b82f6" stopOpacity={0.1} />
            </linearGradient>
            <linearGradient id="colorMemory" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#10b981" stopOpacity={0.8} />
              <stop offset="95%" stopColor="#10b981" stopOpacity={0.1} />
            </linearGradient>
            <linearGradient id="colorDisk" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#f59e0b" stopOpacity={0.8} />
              <stop offset="95%" stopColor="#f59e0b" stopOpacity={0.1} />
            </linearGradient>
            <linearGradient id="colorNetwork" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor="#ef4444" stopOpacity={0.8} />
              <stop offset="95%" stopColor="#ef4444" stopOpacity={0.1} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
          <XAxis
            dataKey="time"
            axisLine={false}
            tickLine={false}
            tick={{ fontSize: 12, fill: 'currentColor' }}
            className="text-gray-500 dark:text-gray-400"
          />
          <YAxis
            axisLine={false}
            tickLine={false}
            tick={{ fontSize: 12, fill: 'currentColor' }}
            className="text-gray-500 dark:text-gray-400"
            domain={[0, 100]}
          />
          <Tooltip content={<CustomTooltip />} />
          <Legend
            wrapperStyle={{
              paddingTop: '20px',
              fontSize: '12px',
            }}
          />
          <Area
            type="monotone"
            dataKey="cpu"
            stroke="#3b82f6"
            fillOpacity={1}
            fill="url(#colorCpu)"
            strokeWidth={2}
            name="CPU"
          />
          <Area
            type="monotone"
            dataKey="memory"
            stroke="#10b981"
            fillOpacity={1}
            fill="url(#colorMemory)"
            strokeWidth={2}
            name="Mémoire"
          />
          <Area
            type="monotone"
            dataKey="disk"
            stroke="#f59e0b"
            fillOpacity={1}
            fill="url(#colorDisk)"
            strokeWidth={2}
            name="Disque"
          />
          <Area
            type="monotone"
            dataKey="network"
            stroke="#ef4444"
            fillOpacity={1}
            fill="url(#colorNetwork)"
            strokeWidth={2}
            name="Réseau"
          />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  )
}

export default SystemMetricsChart