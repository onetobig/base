<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/16
 * Time: 10:15
 */

namespace App\Admin\Extensions;


use Encore\Admin\Form\Field;

class Summernote extends Field
{
    protected $view = 'laravel-admin-summernote::editor';

    protected static $css = [
        'vendor/laravel-admin-ext/summernote/dist/summernote.css',
        'vendor/laravel-admin-ext/summernote/custom.css',
    ];

    protected static $js = [
        'vendor/laravel-admin-ext/summernote/dist/summernote.min.js',
    ];

    public function render()
    {
        $name = $this->formatName($this->column);

        $config = (array) \Encore\Summernote\Summernote::config('config');

        $config = json_encode(array_merge([
            'height' => 300,
        ], $config));

        $token = csrf_token();
        $this->script = <<<EOT

{$this->id} = {};
let config = {$config};
if (!config.callbacks) {
    config.callbacks = {};
}
if (config.upload) {
    config.callbacks.onImageUpload = function(files) {
        var formData = new FormData();
        $.each(files, function(i, file) {
            formData.append("upload_file[]", file);
        });
        
        formData.append("_token", "{$token}")
        $.ajax({
            url: config.upload,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function (data) {
                if(data.errno === 0) {
                    $.each(data.data, function(i, url) {
                        {$this->id}.summernote('insertImage', url, function (image) {
                            image.attr('src', url);
                        });
                    });
                }
            }
        });
    };
}

{$this->id} = $('#{$this->id}').summernote(config);

$('#{$this->id}').on("summernote.change", function (e) {
    var html = $('#{$this->id}').summernote('code');
    $('input[name=$name]').val(html);
});

EOT;
        return parent::render();
    }
}