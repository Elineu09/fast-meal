<?php
/**
 * Queue Service - Intelligent Queue Management
 * 
 * Path: backend/app/services/QueueService.php
 * 
 * Business logic for queue operations:
 * - Calculate average service time
 * - Estimate wait time for users
 * - Get queue status
 * - Analyze queue patterns
 * 
 * INTELLIGENT FEATURE:
 * - Calculates average time from last 10 completed attendances
 * - Estimates current user wait time based on position in queue
 * - Provides real-time queue insights
 */

require_once __DIR__ . '/../repositories/TicketRepository.php';

class QueueService {
    private $ticketRepository;

    /**
     * Initialize QueueService
     */
    public function __construct() {
        $this->ticketRepository = new TicketRepository();
    }

    /**
     * INTELLIGENT FEATURE: Calculate average service time
     * 
     * Analyzes the last 10 completed attendances to determine
     * average time spent per customer, enabling accurate wait time predictions.
     * 
     * @param int $limit Number of completed attendances to analyze (default: 10)
     * 
     * @return array Average time information
     *   - average_seconds: Average duration in seconds
     *   - average_formatted: Human-readable format (MM:SS)
     *   - samples_analyzed: Number of records analyzed
     *   - last_updated: Timestamp of calculation
     * 
     * @throws Exception If database query fails
     */
    public function calculateAverageServiceTime($limit = 10) {
        try {
            $completedAttendances = $this->ticketRepository->getCompletedAttendances($limit);

            // If no completed attendances, return default estimate (3 minutes)
            if (empty($completedAttendances)) {
                return [
                    'average_seconds' => 180,
                    'average_formatted' => '03:00',
                    'samples_analyzed' => 0,
                    'last_updated' => date('Y-m-d H:i:s'),
                    'note' => 'Default estimate (no historical data)'
                ];
            }

            // Calculate average duration
            $totalSeconds = 0;
            foreach ($completedAttendances as $attendance) {
                $totalSeconds += intval($attendance['duration_seconds']);
            }

            $averageSeconds = round($totalSeconds / count($completedAttendances));

            return [
                'average_seconds' => $averageSeconds,
                'average_formatted' => $this->formatSeconds($averageSeconds),
                'samples_analyzed' => count($completedAttendances),
                'last_updated' => date('Y-m-d H:i:s'),
                'min_time' => min(array_column($completedAttendances, 'duration_seconds')),
                'max_time' => max(array_column($completedAttendances, 'duration_seconds'))
            ];
        } catch (Exception $e) {
            throw new Exception('Error calculating average service time: ' . $e->getMessage());
        }
    }

    /**
     * INTELLIGENT FEATURE: Estimate wait time for a specific ticket
     * 
     * Formula:
     * estimated_wait_time = number_of_tickets_ahead * average_service_time
     * 
     * This provides users with realistic expectations about when they'll be served.
     * 
     * @param int $ticketId Ticket ID to estimate wait time for
     * 
     * @return array Wait time estimation
     *   - estimated_wait_seconds: Estimated wait time in seconds
     *   - estimated_wait_formatted: Human-readable format (MM:SS)
     *   - tickets_ahead: Number of tickets ahead in queue
     *   - average_service_time: Average service time used for calculation
     *   - estimated_call_time: Estimated time when ticket will be called
     * 
     * @throws Exception If ticket not found or calculation fails
     */
    public function estimateWaitTime($ticketId) {
        try {
            $ticket = $this->ticketRepository->getById($ticketId);

            if (!$ticket) {
                throw new Exception('Ticket not found');
            }

            // If ticket is not pending or in_attendance, wait time is 0
            if (!in_array($ticket['status'], ['pending', 'in_attendance'])) {
                return [
                    'estimated_wait_seconds' => 0,
                    'estimated_wait_formatted' => '00:00',
                    'tickets_ahead' => 0,
                    'status' => $ticket['status'],
                    'message' => 'This ticket is not in queue'
                ];
            }

            // Get average service time
            $avgStats = $this->calculateAverageServiceTime(10);
            $averageSeconds = $avgStats['average_seconds'];

            // Count tickets ahead
            $ticketsAhead = $this->ticketRepository->getTicketsAhead($ticketId);

            // Calculate estimated wait time
            $estimatedWaitSeconds = $ticketsAhead * $averageSeconds;

            // If ticket is already being attended, wait time is minimal
            if ($ticket['status'] === 'in_attendance') {
                $estimatedWaitSeconds = 0;
            }

            // Calculate estimated call time
            $estimatedCallTime = new DateTime();
            $estimatedCallTime->add(new DateInterval('PT' . $estimatedWaitSeconds . 'S'));

            return [
                'estimated_wait_seconds' => $estimatedWaitSeconds,
                'estimated_wait_formatted' => $this->formatSeconds($estimatedWaitSeconds),
                'tickets_ahead' => $ticketsAhead,
                'average_service_time' => $averageSeconds,
                'average_service_time_formatted' => $avgStats['average_formatted'],
                'estimated_call_time' => $estimatedCallTime->format('Y-m-d H:i:s'),
                'status' => $ticket['status'],
                'ticket_number' => $ticket['ticket_number']
            ];
        } catch (Exception $e) {
            throw new Exception('Error estimating wait time: ' . $e->getMessage());
        }
    }

    /**
     * Get current queue status
     * 
     * Returns comprehensive queue information including:
     * - All pending/in-attendance tickets
     * - Estimated times for each
     * - Queue statistics
     * - Average service time
     * 
     * @return array Queue status
     */
    public function getQueueStatus() {
        try {
            $pendingTickets = $this->ticketRepository->getPendingQueue();
            $stats = $this->ticketRepository->getQueueStats();
            $avgStats = $this->calculateAverageServiceTime(10);

            $queueWithEstimates = [];
            
            foreach ($pendingTickets as $index => $ticket) {
                $ticketsAhead = $index; // Simplified: index shows position
                $estimatedWaitSeconds = $ticketsAhead * $avgStats['average_seconds'];

                $queueWithEstimates[] = [
                    'id' => $ticket['id'],
                    'ticket_number' => $ticket['ticket_number'],
                    'type' => $ticket['type'],
                    'status' => $ticket['status'],
                    'user_name' => $ticket['user_name'],
                    'position' => $index + 1,
                    'tickets_ahead' => $ticketsAhead,
                    'estimated_wait_seconds' => $estimatedWaitSeconds,
                    'estimated_wait_formatted' => $this->formatSeconds($estimatedWaitSeconds),
                    'created_at' => $ticket['created_at']
                ];
            }

            return [
                'queue' => $queueWithEstimates,
                'statistics' => [
                    'total_pending' => intval($stats['total_pending']),
                    'priority_tickets' => intval($stats['priority_count']),
                    'normal_tickets' => intval($stats['normal_count']),
                    'average_service_time' => $avgStats['average_seconds'],
                    'average_service_time_formatted' => $avgStats['average_formatted'],
                    'longest_wait_estimated' => $avgStats['average_seconds'] * intval($stats['total_pending']),
                    'longest_wait_formatted' => $this->formatSeconds($avgStats['average_seconds'] * intval($stats['total_pending']))
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            throw new Exception('Error fetching queue status: ' . $e->getMessage());
        }
    }

    /**
     * Get queue position and details for a specific user
     * 
     * @param int $userId User ID
     * 
     * @return array User's position in queue with details
     * @throws Exception If user has no active ticket
     */
    public function getUserQueuePosition($userId) {
        try {
            $activeTicket = $this->ticketRepository->getActiveTicketByUser($userId);

            if (!$activeTicket) {
                return [
                    'has_active_ticket' => false,
                    'message' => 'No active ticket in queue'
                ];
            }

            $waitEstimate = $this->estimateWaitTime($activeTicket['id']);
            $queueStats = $this->ticketRepository->getQueueStats();

            return array_merge($waitEstimate, [
                'has_active_ticket' => true,
                'total_in_queue' => intval($queueStats['total_pending'])
            ]);
        } catch (Exception $e) {
            throw new Exception('Error getting user queue position: ' . $e->getMessage());
        }
    }

    /**
     * Format seconds to MM:SS format
     * 
     * @param int $seconds Total seconds
     * 
     * @return string Formatted time string
     */
    private function formatSeconds($seconds) {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        
        return str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get queue analytics
     * Useful for admin dashboard and reports
     * 
     * @return array Queue analytics
     */
    public function getQueueAnalytics() {
        try {
            $queueStatus = $this->getQueueStatus();
            $avgStats = $this->calculateAverageServiceTime(10);

            $totalWaitTime = 0;
            foreach ($queueStatus['queue'] as $item) {
                $totalWaitTime += $item['estimated_wait_seconds'];
            }

            $averageWaitTime = count($queueStatus['queue']) > 0 
                ? round($totalWaitTime / count($queueStatus['queue'])) 
                : 0;

            return [
                'queue_size' => count($queueStatus['queue']),
                'average_wait_time' => $averageWaitTime,
                'average_wait_time_formatted' => $this->formatSeconds($averageWaitTime),
                'total_estimated_queue_time' => $totalWaitTime,
                'average_service_time' => $avgStats['average_seconds'],
                'priority_tickets_count' => $queueStatus['statistics']['priority_tickets'],
                'normal_tickets_count' => $queueStatus['statistics']['normal_tickets'],
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            throw new Exception('Error calculating queue analytics: ' . $e->getMessage());
        }
    }
}
?>
