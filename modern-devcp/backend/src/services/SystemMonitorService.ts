import { exec } from 'child_process'
import { promisify } from 'util'
import { logger } from '@/utils/logger'
import { config } from '@/config/config'

const execAsync = promisify(exec)

export interface SystemMetrics {
  cpu: {
    usage: number
    cores: number
    loadAverage: number[]
  }
  memory: {
    total: number
    used: number
    free: number
    usage: number
  }
  disk: {
    total: number
    used: number
    free: number
    usage: number
  }
  network: {
    bytesIn: number
    bytesOut: number
  }
  uptime: number
  timestamp: Date
}

export class SystemMonitorService {
  private intervalId: NodeJS.Timeout | null = null
  private isRunning = false

  public start(callback: (metrics: SystemMetrics) => void): void {
    if (this.isRunning) {
      logger.warn('System monitor is already running')
      return
    }

    this.isRunning = true
    logger.info('Starting system monitor service')

    // Initial metrics collection
    this.collectMetrics().then(callback).catch(logger.error)

    // Set up interval for periodic collection
    this.intervalId = setInterval(async () => {
      try {
        const metrics = await this.collectMetrics()
        callback(metrics)
      } catch (error) {
        logger.error('Error collecting system metrics', error)
      }
    }, config.monitoring.interval)
  }

  public stop(): void {
    if (!this.isRunning) {
      return
    }

    this.isRunning = false
    logger.info('Stopping system monitor service')

    if (this.intervalId) {
      clearInterval(this.intervalId)
      this.intervalId = null
    }
  }

  private async collectMetrics(): Promise<SystemMetrics> {
    try {
      const [cpuInfo, memoryInfo, diskInfo, networkInfo] = await Promise.all([
        this.getCPUMetrics(),
        this.getMemoryMetrics(),
        this.getDiskMetrics(),
        this.getNetworkMetrics(),
      ])

      return {
        cpu: cpuInfo,
        memory: memoryInfo,
        disk: diskInfo,
        network: networkInfo,
        uptime: process.uptime(),
        timestamp: new Date(),
      }
    } catch (error) {
      logger.error('Error collecting system metrics', error)
      throw error
    }
  }

  private async getCPUMetrics(): Promise<SystemMetrics['cpu']> {
    try {
      // Get CPU usage using top command
      const { stdout } = await execAsync("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1")
      const usage = parseFloat(stdout.trim()) || 0

      // Get number of CPU cores
      const { stdout: coresOutput } = await execAsync('nproc')
      const cores = parseInt(coresOutput.trim(), 10) || 1

      // Get load average
      const { stdout: loadOutput } = await execAsync("uptime | awk -F'load average:' '{print $2}'")
      const loadAverage = loadOutput.trim().split(',').map(val => parseFloat(val.trim()) || 0)

      return {
        usage,
        cores,
        loadAverage,
      }
    } catch (error) {
      logger.error('Error getting CPU metrics', error)
      return { usage: 0, cores: 1, loadAverage: [0, 0, 0] }
    }
  }

  private async getMemoryMetrics(): Promise<SystemMetrics['memory']> {
    try {
      const { stdout } = await execAsync("free -b | grep '^Mem:'")
      const memInfo = stdout.trim().split(/\s+/)
      
      const total = parseInt(memInfo[1], 10) || 0
      const used = parseInt(memInfo[2], 10) || 0
      const free = parseInt(memInfo[3], 10) || 0
      const usage = total > 0 ? (used / total) * 100 : 0

      return {
        total,
        used,
        free,
        usage,
      }
    } catch (error) {
      logger.error('Error getting memory metrics', error)
      return { total: 0, used: 0, free: 0, usage: 0 }
    }
  }

  private async getDiskMetrics(): Promise<SystemMetrics['disk']> {
    try {
      const { stdout } = await execAsync("df -B1 / | tail -1")
      const diskInfo = stdout.trim().split(/\s+/)
      
      const total = parseInt(diskInfo[1], 10) || 0
      const used = parseInt(diskInfo[2], 10) || 0
      const free = parseInt(diskInfo[3], 10) || 0
      const usage = total > 0 ? (used / total) * 100 : 0

      return {
        total,
        used,
        free,
        usage,
      }
    } catch (error) {
      logger.error('Error getting disk metrics', error)
      return { total: 0, used: 0, free: 0, usage: 0 }
    }
  }

  private async getNetworkMetrics(): Promise<SystemMetrics['network']> {
    try {
      // This is a simplified implementation
      // In a real scenario, you'd want to track network interface statistics
      const { stdout } = await execAsync("cat /proc/net/dev | grep -E '(eth0|ens|enp)' | head -1")
      
      if (stdout.trim()) {
        const netInfo = stdout.trim().split(/\s+/)
        const bytesIn = parseInt(netInfo[1], 10) || 0
        const bytesOut = parseInt(netInfo[9], 10) || 0

        return { bytesIn, bytesOut }
      }

      return { bytesIn: 0, bytesOut: 0 }
    } catch (error) {
      logger.error('Error getting network metrics', error)
      return { bytesIn: 0, bytesOut: 0 }
    }
  }

  public async getQuickMetrics(): Promise<Partial<SystemMetrics>> {
    try {
      return await this.collectMetrics()
    } catch (error) {
      logger.error('Error getting quick metrics', error)
      return {
        cpu: { usage: 0, cores: 1, loadAverage: [0, 0, 0] },
        memory: { total: 0, used: 0, free: 0, usage: 0 },
        disk: { total: 0, used: 0, free: 0, usage: 0 },
        network: { bytesIn: 0, bytesOut: 0 },
        uptime: process.uptime(),
        timestamp: new Date(),
      }
    }
  }
}