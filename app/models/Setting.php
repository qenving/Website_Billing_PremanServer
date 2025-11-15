<?php

class Setting extends Model {
    protected $table = 'settings';

    public function getValue($key, $default = null) {
        $setting = $this->first('key', '=', $key);
        return $setting ? $setting['value'] : $default;
    }

    public function setValue($key, $value, $type = 'string', $group = 'general') {
        $existing = $this->first('key', '=', $key);

        if ($existing) {
            return $this->execute(
                "UPDATE {$this->table} SET value = :value WHERE key = :key",
                ['value' => $value, 'key' => $key]
            );
        } else {
            return $this->create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'group' => $group
            ]);
        }
    }

    public function getByGroup($group) {
        return $this->where('group', '=', $group);
    }
}
