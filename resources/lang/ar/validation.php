<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'حقل :attribute يجب ان يكون مقبول .',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'حقل :attribute ممكن ان يحتوى على ارقام وحروف.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
         'numeric' => 'حقل :attribute يجب أن يكون بين :min و :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'حقل :attribute يجب أن يكون بين :min و :max حرف.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'حقل :attribute يجب ان يكون صح او خطا.',
    'confirmed'            => 'حقل :attribute لا يطابق المواصفات.',
    'date'                 => 'حقل :attribute يحتوى تاريخ غير صالح.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'هذا الحقل يجب ان يكون  :digits ارقام.',
    'digits_between'       => 'حقل :attribute يجب أن يكون بين :min و :max أرقام.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'حقل :attribute يحتوى قيمه متكرره.',
    'email'                => ' :attribute يجب ان يكون صالح .',
    'exists'               => 'حقل  :attribute غير صالح.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'حقل  :attribute غير صالح.',
    'in_array'             => 'حقل :attribute لا يوجد فى :other.',
    'integer'              => 'حقل :attribute يجب ان يكون رقم.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'حقل :attribute يجب ان يكون نص جسون.',
    'max'                  => [
        'numeric' => 'حقل :attribute لا يجب انيكون اكبر من  :max.',
        'file'    => 'حقل :attribute لا يجب ان يكون اكبر من  :max kilobytes.',
        'string'  => 'حقل :attribute لا يجب ان يكون اكبر من  :max characters.',
        'array'   => 'حقل :attribute لا يجب ان يكون اكبر من  :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'حقل :attribute يجب ان يكون عالاقل :min.',
        'file'    => 'حقل :attribute يجب ان يكون عالاقل :min كيلوبايت.',
        'string'  => 'حقل :attribute يجب ان يكون عالاقل :min حروف.',
        'array'   => 'حقل  :attribute يجب ان يكون عالاقل :min items.',
    ],
    'not_in'               => 'حقل  :attribute يحتوى قيمه غير صالحه.',
    'numeric'              => 'حقل :attribute يجب ان يكون رقم .',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'حقل :attribute الترميز غير صالح.',
    'required'             => ':attribute هذا الحقل مطلوب',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'برجاء التحقق من الحقلين غير متماثلين.',
    'size'                 => [
        'numeric' => 'حقل :attribute يجب ان يكون :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
     'string'               => 'حقل :attribute يجب أن يكون نص .',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'هذا :attribute محجوز بالفعل',
    'uploaded'             => 'حقل :attribute فشل فى التحميل.',
    'url'                  => 'The :attribute format is invalid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [


        'new_type' => 'نوع جديد',
        'court' => 'محكمه',
        'govs' => 'المحافظه',
        'cities' => 'المدينه',
        'creditcard_number'=>'رقم الكريدت كارد',
        'creditcard_month'=>'شهر الكريديت كارد',
        'creditcard_year'=>'سنه الكريديت كارد',
        'name'=>'الاسم',
        'email'=>'الايميل',
        'creditcard_cvv'=>'رقم الاس فى فى',
        'main' => 'التصنيف الرئيسي',
        'mains' => 'التصنيف الرئيسي',
        'subs' => 'التصنيف الفرعي',
        'gov_name' => 'إسم المحافظة',
        'government_name' => 'إسم المحافظة',
        'city_name'=> 'إسم المدينة',
        'contract_name'=>'اسم العقد',
        'image'=>'الصوره',
        'file'=>'الملف',
        'is_contract'=>'الصيغه أو العقد',
        'newsName'  => 'عنوان الاخبار',
        'newsContent' => 'تفاصيل الخبر',
        'user_name'=>'اسم المستخدم',
        'full_name'=>'الاسم بالكامل',
        'role'=>'دور المستخدم',
        'email'=>'البريد الإلكترونى',
        'phone'=>'الهاتف',
        'mobile'=>'الموبايل',
        'password'=>'كلمه المرور',
        'confirm_password'=>'تأكيد كلمه المرور',
        'code'=>'الكود',
        'address '=>'العنوان',
        'birthdate'=>'يوم الميلاد',
        'interests'=>'الاهتمامات'


],

];
