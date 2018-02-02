<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => ':attribute ကျနော်တို့ကိုလက်ခံရပါမည်。',
    'active_url'           => ':attribute ဒါဟာတရားဝင် URL ကိုမဟုတ်ပါဘူး。',
    'after'                => ':attribute ဒါဟာအတွက်ဖြစ်ရပါမည် :date ဒါဟာအတွက်ဖြစ်ရပါမည်。',
    'alpha'                => ':attribute သာအက္ခရာများအားဖြင့်。',
    'alpha_dash'           => ':attribute သာအက္ခရာများ, နံပါတ်များနှင့်မျဉ်းစောင်းများထားရှိရေးနိုင်ပါတယ်။',
    'alpha_num'            => ':attribute ကိုယ်ကသာအက္ခရာများနှင့်နံပါတ်များထားရှိရေးနိုင်ပါတယ်。',
    'array'                => ':attribute ဒါဟာတစ်ခင်းကျင်းဖြစ်ရမည်。',
    'before'               => ':attribute ဒါဟာအတွက်ဖြစ်ရပါမည် :date ရက်စွဲခြင်းမပြုမီ。',
    'between'              => [
        'numeric' => ':attribute သငျသညျအကြားဖြစ်ရပါမည် :min - :max အကြား。',
        'file'    => ':attribute သငျသညျအကြားဖြစ်ရပါမည် :min - :max kb အကြား。',
        'string'  => ':attribute သငျသညျအကြားဖြစ်ရပါမည် :min - :max ဇာတ်ကောင်များအကြား。',
        'array'   => ':attribute သာရပါမည် :min - :max ယူနစ်。',
    ],
    'boolean'              => ':attribute သင်တစ်ဦး boolean value ကိုသူဖြစ်ရမည်。',
    'confirmed'            => ':attribute နှစ်ခု entries တွေကိုကိုက်ညီမှုရှိပါတယ်。',
    'date'                 => ':attribute ဒါဟာမှန်ကန်သောရက်စွဲတစ်ခုမဟုတ်ပါဘူး。',
    'date_format'          => ':attribute ပုံစံဖြစ်ရပါမည် :format。',
    'different'            => ':attribute နှင့် :other ကွဲပြားခြားနားသောဖြစ်ရမည်。',
    'digits'               => ':attribute ဖြစ်ရမည် :digits ဂဏန်း。',
    'digits_between'       => ':attribute အကြားဖြစ်ရမည် :min နှင့် :max ဂဏန်း。',
    'distinct'             => ':attribute ယခုပင်လျှင်တည်ရှိ。',
    'email'                => ':attribute ဒါဟာတရားဝင် e-mail, မဟုတျပါဘူး。',
    'exists'               => ':attribute မတည်ရှိပါဘူး。',
    'filled'               => ':attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'image'                => ':attribute ရုပ်ပုံများဖြစ်ရမည်。',
    'in'                   => 'Selected ဂုဏ်သတ္တိများ :attribute တရားမဝင်သော。',
    'in_array'             => ':attribute မဟုတ် :other တွင်。',
    'integer'              => ':attribute ၎င်းသည်ကိန်းဖြစ်ရမည်。',
    'ip'                   => ':attribute ၎င်းသည်ကိန်းဖြစ်ရမည်。',
    'json'                 => ':attribute မှန်ကန်သော JSON format နဲ့ဖြစ်ရမည်。',
    'max'                  => [
        'numeric' => ':attribute ထက်မကျော်လွန် :max。',
        'file'    => ':attribute ထက်မကျော်လွန် :max kb。',
        'string'  => ':attribute ထက်မကျော်လွန် :max ဇာတ်ကောင်များ。',
        'array'   => ':attribute အများဆုံး :max ယူနစ်。',
    ],
    'mimes'                => ':attribute ဒါဟာသူဖြစ်ရမည် :values ဖိုင်အမျိုးအစား。',
    'min'                  => [
        'numeric' => ':attribute ဒါဟာထက် သာ. ကြီးမြတ်သို့မဟုတ်တန်းတူဖြစ်ရမည် :min。',
        'file'    => ':attribute Size ကိုထက်သေးငယ်မဖွစျနိုငျ :min kb。',
        'string'  => ':attribute အနည်းဆုံး :min ဇာတ်ကောင်များ。',
        'array'   => ':attribute အနည်းဆုံးရှိပါတယ် :min ယူနစ်。',
    ],
    'not_in'               => 'Selected ဂုဏ်သတ္တိများ :attribute တရားမဝင်သော。',
    'numeric'              => ':attribute ဒါဟာဂဏန်းတစ်ခုဖြစ်ရမည်。',
    'present'              => ':attribute ပစ္စုပ္ပန်ဖြစ်ရမည်。',
    'regex'                => ':attribute မှားယွင်းနေ format နဲ့。',
    'required'             => ':attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'required_if'          => 'ဘယ်အချိန်မှာ :other ဖြစ် :value ဘယ်အချိန်မှာ :attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'required_unless'      => 'ဘယ်အချိန်မှာ :other ဒါဟာမဖြစ် :value ဘယ်အချိန်မှာ :attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'required_with'        => 'ဘယ်အချိန်မှာ :values ရှိပါတယ် :attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'required_with_all'    => 'ဘယ်အချိန်မှာ :values ရှိပါတယ် :attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'required_without'     => ':attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'required_without_all' => 'ဘယ်အချိန်မှာ :values မရှိပါ :attribute အချည်းနှီးသောမဖွစျနိုငျ。',
    'same'                 => ':attribute နှင့် :other ဒါဟာတူညီတဲ့ဖြစ်ရမည်。',
    'size'                 => [
        'numeric' => ':attribute Size ကိုသူဖြစ်ရမည် :size。',
        'file'    => ':attribute Size ကိုသူဖြစ်ရမည် :size kb。',
        'string'  => ':attribute ဇာတ်ကောင်များ :size ဇာတ်ကောင်များ。',
        'array'   => ':attribute ဇာတ်ကောင်များ :size ယူနစ်。',
    ],
    'string'               => ':attribute ဒါဟာ string ကိုသူဖြစ်ရမည်。',
    'timezone'             => ':attribute ဒါဟာတရားဝင်အချိန်ဇုန်တန်ဖိုးများဖြစ်ရမည်。',
    'unique'               => ':attribute ယခုပင်လျှင်တည်ရှိ。',
    'url'                  => ':attribute မှားယွင်းနေ format နဲ့。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention 'attribute.rule' to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'ထုံးစံ-သတင်းစကား',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of 'email'. This simply helps us make messages a little cleaner.
    |
    */

    'attributes'           => [
        'name'                  => 'အမည်',
        'username'              => 'username',
        'user_name'              => 'ဝင်မည်',
        'email'                 => 'စာတိုက်ပုံး',
        'first_name'            => 'အမည်',
        'last_name'             => 'မြိုးရိုးအမညျ',
        'password'              => 'Password ကို',
        'password_confirmation' => 'စကားဝှက်ကိုအတည်ပြုပါ',
        'city'                  => 'မြို့',
        'country'               => 'ပြည်',
        'address'               => 'လိပ်စာ',
        'phone'                 => 'ဖုန်းနံပါတ်',
        'mobile'                => 'ဆယ်လူလာဖုန်းကို',
        'age'                   => 'Young က',
        'sex'                   => 'ကျား, မ',
        'gender'                => 'ကျား, မ',
        'day'                   => 'နေ့',
        'month'                 => 'လ',
        'year'                  => 'ခုနှစ်',
        'hour'                  => 'ဘယ်အချိန်မှာ',
        'minute'                => 'ခှဲခွား',
        'second'                => 'ဒုတိယ',
        'title'                 => 'ခေါင်းစဉ်',
        'content'               => 'အကြောင်းအရာ',
        'description'           => 'ဖေါ်ပြချက်',
        'excerpt'               => 'အကျဉ်းချုပ်',
        'date'                  => 'နေ့စှဲ',
        'time'                  => 'အချိန်',
        'available'             => 'ရရှိနိုင်',
        'size'                  => 'အရွယ်',
        'cat_id'                => 'အားကစားပြိုင်ပွဲအမျိုးအစား',
        'game_id'               => 'ဂိမ်း',
        'hall_type'             => 'ခန်းမ',
        'max_money'             => 'မျက်နှာကြက်',
        'min_money'             => 'အနည်းဆုံး',
        'username_md'           => 'ကစားသမားများ',
        'password_md'           => 'Password ကို',
        'password_md_confirmation'           => 'စကားဝှက်ကိုအတည်ပြုပါ',
        'agent_id'              => 'အေးဂျင့်များ',
        'language'              => 'ဘာသာစကား',
        'account_state'         => 'ပြည်နယ်',
        'area'                  => 'ဒေသ',
        'time_zone'             => 'အချိန်ဇုန်',
        'lang_code'             => 'ဘာသာစကား',
        'grade_id'              => 'အေးဂျင့်ရိုက်ထည့်ပါ',
        'money'                 => 'ပိုက်ဆံ',
        'status'                => 'ပုံစံ',
        'game_name'             => 'ဂိမ်းခေါင်းစဉ်',
        'items'                 => 'ဒေသဆိုင်ရာတန်ဖိုးကိုအလောင်းအစား',
        'agent_domain'          => 'အေးဂျင့်ဒိုမိန်း',
        'ip_info'                 => 'IP',
        'captcha' => 'verification code ကို',

    ],

];
