<?php

class SMSMessagePreset extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms_messages_presets';

    public function getPresets()
    {
        return self::where('category', $this->category)->get();
    }

    public static function categories()
    {
        return self::groupBy('category')->orderBy('id')->get();
    }

    public static function getCategoryPresets($category, $customer_id)
    {
        $presets = self::where('category', $category)->get();
        $customer = Customer::find($customer_id);

        if ($customer) {
            $tariff = Tariff::where('scheme_number', $customer->scheme_number)->first();
            foreach ($presets as $k => $p) {
                $p->body = str_replace('%first_name%', $customer->first_name, $p->body);
                $p->body = str_replace('%surname%', $customer->surname, $p->body);
                $p->body = str_replace('%mobile%', $customer->mobile_number, $p->body);
                $p->body = str_replace('%balance%', number_format($customer->balance, 2), $p->body);
                if ($tariff) {
                    $p->body = str_replace('%standing_charge%', $tariff->tariff_2, $p->body);
                    $p->body = str_replace('%kwh_charge%', $tariff->tariff_1, $p->body);
                    $p->body = str_replace('%per_kwh%', $tariff->tariff_1, $p->body);
                }
                if ($customer->districtMeter) {
                    $p->body = str_replace('%temp%', $customer->districtMeter->last_flow_temp, $p->body);
                }
            }
        }

        return $presets;
    }
}
