<?php

class SystemGraphData extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'system_graph_data';

    public function modify($index, $new_value)
    {
        try {
            if ($this->isEmpty()) {
                throw new Exception("Empty set. Index $index not found.");

                return;
            } else {
                $data = unserialize($this->data);

                if (! is_array($data)) {
                    throw new Exception("Cannot modify index $index. Data set is not an array.");

                    return;
                }

                $data[$index] = $new_value;

                $data = serialize($data);

                $this->data = $data;
                $this->save();
            }
        } catch (Exception $e) {
            return [
                'error' => 'An error occured: '.$e->getMessage(),
            ];
        }
    }

    public function get($index)
    {
        try {
            if ($this->isEmpty()) {
                throw new Exception("Empty set. Index $index not found.");

                return;
            } else {
                $data = unserialize($this->data);

                if (! is_array($data)) {
                    throw new Exception("Cannot get index $index. Data set is not an array.");

                    return;
                }

                return $data[$index];
            }
        } catch (Exception $e) {
            return [
                'error' => 'An error occured: '.$e->getMessage(),
            ];
        }
    }

    public function contains($index)
    {
        try {
            if ($this->isEmpty()) {
                return false;
            } else {
                $data = unserialize($this->data);

                if (! is_array($data)) {
                    return false;
                }

                if (! isset($data[$index])) {
                    return false;
                }

                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function set($index, $value)
    {
        try {
            if ($this->isEmpty()) {
                $data = [];

                $data[$index] = $value;

                $this->data = serialize($data);

                $this->save();
            } else {
                if (@unserialize($this->data) === false) {
                    $data = [];
                //throw new Exception("reset array");
                } else {
                    $data = unserialize($this->data);
                }

                if (! is_array($data)) {
                    $this->data = [];
                }

                $data[$index] = $value;

                $data = serialize($data);

                $this->data = $data;
                $this->save();
            }
        } catch (Exception $e) {
            return [
                'error' => 'An error occured: '.$e->getMessage(),
            ];
        }
    }

    public function isEmpty()
    {
        return  empty($this->data) || (strlen(preg_replace('/\s+/', ' ', $this->data)) == 0);
    }

    public function percent($figure1, $figure2, $formatted = true)
    {
        $change = $figure1 - $figure2;
        $percent = ($change / $figure2) * 100;

        if ($formatted) {
            if ($percent > 0.0) {
                return '<font color="#2dce89"><i class="fa fa fa-arrow-up"></i> '.number_format($percent, 0).'%</font>';
            }

            if ($percent < 0.0) {
                return '<font color="#f5365c"><i class="fa fa fa-arrow-down"></i> '.abs(number_format($percent, 0)).'%</font>';
            }

            if ($percent == 0) {
                return '<font color="#b9b9b9"><i class="fa fa fa-grip-lines"></i> '.abs(number_format($percent, 0)).'%</font>';
            }
        }

        return number_format($percent, 0);
    }
}
