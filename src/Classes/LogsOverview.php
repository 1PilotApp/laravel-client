<?php

namespace OnePilot\Client\Classes;

use Carbon\Carbon;
use SplFileObject;

class LogsOverview extends LogsFiles
{
    private $intervalDates = [];
    private $overview = [];

    /** @return array */
    public function get()
    {
        foreach (self::LOGS_OVERVIEW_INTERVALS as $interval) {
            $this->intervalDates[$interval] = Carbon::now()->subMinutes($interval)->format('Y-m-d H\:i\:s');
        }

        foreach ($this->getLogsFiles() as $filePath) {
            $file = new SplFileObject($filePath, 'r');

            $this->logsOverviewOfFile($file);
        }

        $overview = [];

        foreach ($this->overview as $interval => $levels) {
            $overview[$interval] = [];

            foreach ($levels as $level => $total) {
                $overview[$interval][] = [
                    'level' => $level,
                    'total' => $total,
                ];
            }
        }

        return $overview;
    }

    private function logsOverviewOfFile(SplFileObject $file)
    {
        while (!$file->eof()) {
            $line = $file->fgets();

            if (!isset($line[0]) || $line[0] != '[') {
                continue;
            }

            if (!preg_match(self::LOG_PATTERN, $line, $matches)) {
                continue;
            }

            if (empty($matches['date']) || empty($matches['level'])) {
                continue;
            }

            foreach ($this->intervalDates as $interval => $date) {
                if ($matches['date'] >= $date) {
                    $this->incrementLogsOverview($interval, $matches['level']);
                }
            }
        }
    }

    private function incrementLogsOverview($interval, $level)
    {
        $level = strtolower($level);

        if (!in_array($level, self::LEVELS)) {
            return;
        }

        if (!isset($this->overview[$interval][$level])) {
            $this->overview[$interval][$level] = 0;
        }

        $this->overview[$interval][$level]++;
    }
}
