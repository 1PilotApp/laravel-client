<?php

namespace OnePilot\Client\Classes;

use Carbon\Carbon;
use SplFileObject;

class LogsBrowser extends LogsOverview
{
    /** @var array */
    private $logs = [];

    /** @var int */
    private $currentPage;

    /** @var int */
    private $perPage;

    /** @var int */
    private $from;

    /** @var int */
    private $to;

    /** @var int */
    private $total = 0;

    /** @var string|null Date in Y-m-d H:i:s format */
    private $dateFrom = null;

    /** @var string|null Date in Y-m-d H:i:s format */
    private $dateTo = null;

    /** @var string|null */
    private $searchString;

    /** @var array|null */
    private $levelsFilter;

    public function setPagination(int $currentPage = 1, int $perPage = 20)
    {
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->from = (($currentPage - 1) * $perPage) + 1;
        $this->to = (($currentPage - 1) * $perPage) + $perPage;
    }

    /** @return array */
    public function getPagination()
    {
        return [
            'current_page' => $this->currentPage,
            'per_page'     => $this->perPage,
            'from'         => empty($this->logs) ? null : $this->from,
            'to'           => empty($this->logs) ? null : $this->from + count($this->logs) - 1,
            'total'        => $this->total,
            'last_page'    => (int)ceil($this->total / $this->perPage),
        ];
    }

    /** @return array */
    public function get()
    {
        foreach ($this->getLogsFiles() as $filePath) {
            $file = new SplFileObject($filePath, 'r');

            $this->browseFile($file);
        }

        return $this->logs;
    }

    private function browseFile(SplFileObject $file)
    {
        $fileIndex = [];

        while (!$file->eof()) {
            $position = $file->ftell();
            $line = $file->current();

            if (isset($line[0]) && $line[0] == '[') {
                $fileIndex[] = $position;
            }

            $file->next();
        }

        $fileIndex = array_reverse($fileIndex, true);

        foreach ($fileIndex as $row => $position) {
            $file->fseek($position);
            $line = $file->current();

            if (!isset($line[0]) || $line[0] != '[') {
                continue;
            }

            if (!preg_match(self::LOG_PATTERN, $line, $matches)) {
                continue;
            }

            if (empty($date = $matches['date']) || empty($level = $matches['level']) || empty($message = $matches['message'])) {
                continue;
            }

            if (!$this->matchFilters($date, $level, $message)) {
                continue;
            }

            $this->total++;

            if ($this->total < $this->from || $this->total > $this->to) {
                continue;
            }

            $stackTrace = "";
            $stackTraceRow = 1;

            if (!$file->eof()) {
                $file->next();

                while (!$file->eof()) {
                    $stackLine = $file->fgets();

                    if ($stackTraceRow >= 80) {
                        break;
                    }

                    if (preg_match(self::LOG_PATTERN, $stackLine)) {
                        break;
                    }

                    $stackTrace .= $stackLine;
                    $stackTraceRow++;
                }
            }

            $this->logs[] = [
                'date'    => $date,
                'level'   => $level,
                'channel' => $matches['channel'] ?: null,
                'message' => $stackTrace ? $message . PHP_EOL . $stackTrace : $message,
            ];
        }
    }

    public function setRange(string $from = null, string $to = null)
    {
        $this->dateFrom = $from ? Carbon::parse($from)->format('Y-m-d H\:i\:s') : null;
        $this->dateTo = $to ? Carbon::parse($to)->format('Y-m-d H\:i\:s') : null;
    }

    public function setSearch(string $search = null)
    {
        $this->searchString = $search;
    }

    public function setLevels(array $levels = null)
    {
        if (empty($levels)) {
            return;
        }

        array_walk($levels, function (&$value) {
            $value = strtolower($value);
        });

        $this->levelsFilter = $levels;
    }

    /**
     * @param string $date
     * @param string $level
     * @param string $message
     *
     * @return bool
     */
    private function matchFilters($date, $level, $message)
    {
        if (!empty($this->dateFrom) && $date < $this->dateFrom) {
            return false;
        }

        if (!empty($this->dateTo) && $date > $this->dateTo) {
            return false;
        }

        if (!empty($this->levelsFilter) && !in_array(strtolower($level), $this->levelsFilter)) {
            return false;
        }

        if (!empty($this->searchString) && stripos($message, $this->searchString) === false) {
            return false;
        }

        return true;
    }
}
