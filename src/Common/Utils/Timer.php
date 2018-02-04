<?php

namespace uSIreF\Common\Utils;

/**
 * This file defines class for Timer utility.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Timer {

    /**
     * Definition of result types.
     */
    const RESULT_SUM   = 'sum';
    const RESULT_AVG   = 'avg';
    const RESULT_COUNT = 'count';

    /**
     * @var array
     */
    private $_data = [];

    /**
     * Start timer with given mark.
     *
     * @param string $mark Mark name
     *
     * @return Timer
     */
    public function start(string $mark): Timer {
        $this->_data[$mark]['start'][] = $this->_getTime();

        return $this;
    }

    /**
     * Stop timer with given mark.
     *
     * @param string $mark Mark name
     *
     * @return Timer
     */
    public function stop(string $mark): Timer {
        $this->_data[$mark]['stop'][] = $this->_getTime();

        return $this;
    }

    /**
     * Clean all timer data.
     *
     * @return Timer
     */
    public function clean(): Timer {
        $this->_data = [];

        return $this;
    }

    /**
     * Returns measured data by given result type.
     *
     * @param string $type Result type (one of Timer::RESULT_*)
     *
     * @return array
     */
    public function result(string $type = self::RESULT_SUM): array {
        $result = [];
        foreach ($this->_data as $name => $data) {
            $data = $this->_pairData($data);

            switch ($type) {
                case self::RESULT_SUM:
                    $result[$name] = array_sum($data);
                    break;
                case self::RESULT_AVG:
                    $result[$name] = array_sum($data) / count($data);
                    break;
                case self::RESULT_COUNT:
                    $result[$name] = count($data);
                    break;
            }
        }

        return $result;
    }

    /**
     * Returns paired data from measured data.
     *
     * @param array $data Measured data
     *
     * @return array
     */
    private function _pairData(array $data): array {
        $result = [];
        foreach ($data['start'] as $key => $start) {
            if ($data['stop'][$key]) {
                $stop     = $data['stop'][$key];
                $result[] = $stop - $start;
            }
        }

        return $result;
    }

    /**
     * Get current time in microseconds.
     *
     * @return float
     */
    private function _getTime(): float {
        return microtime(true) * 1000;
    }
}
