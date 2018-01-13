<?php

namespace uSIreF\Common\Utils;

class Timer {

    const SUM   = 'sum';
    const AVG   = 'avg';
    const COUNT = 'count';

    private $_data = [];

    public function start(string $mark): Timer {
        $this->_data[$mark]['start'][] = $this->_getTime();

        return $this;
    }

    public function stop(string $mark): Timer {
        $this->_data[$mark]['stop'][] = $this->_getTime();

        return $this;
    }

    public function clean() {
        $this->_data = [];
    }

    public function result($type = self::SUM): array {
        $result = [];
        foreach ($this->_data as $name => $data) {
            $data = $this->_pairData($data);

            switch ($type) {
                case self::SUM:
                    $result[$name] = array_sum($data);
                    break;
                case self::AVG:
                    $result[$name] = array_sum($data) / count($data);
                    break;
                case self::COUNT:
                    $result[$name] = count($data);
                    break;
            }
        }

        return $result;
    }

    private function _pairData($data) {
        $result = [];
        foreach ($data['start'] as $key => $start) {
            if ($data['stop'][$key]) {
                $stop     = $data['stop'][$key];
                $result[] = $stop - $start;
            }
        }

        return $result;
    }

    private function _getTime(): float {
        return microtime(true) * 1000;
    }
}
