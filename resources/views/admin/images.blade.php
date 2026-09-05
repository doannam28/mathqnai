<div class="form-group {!! !$errors->has($errorKey) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>

    <div class="col-sm-8">
        @include('admin::form.error')

        <table class="table table-has-many has-many-images">
            <thead>
            <tr>
                <th>Hình ảnh</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody style="display:none;" id="template_{{str_replace(['[', ']'], '_', $name)}}">
            <tr>
                <td>
                    <div class="preview file-preview" style="display: none; overflow: auto">
                        <div class="card file-preview-frame krajee-default  kv-preview-thumb">
                            <img src="" class="img" style="max-width: 100px">
                        </div>
                    </div>
                    <div class="input-group file-caption-main">
                        <input type="hidden" class="form-control remove-image"
                               name="{{$name}}[__key_images__][_remove_]" value="0">
                        <input type="text" readonly class="form-control" name="{{$name}}[__key_images__][name]"
                               placeholder="Select image">
                        <input type="file" class="form-control file-image " name="{{$name}}[__key_images__][url]"
                               style="display: none" placeholder="Select image">
                        <div class="input-group-btn input-group-append">
                            <button class="btn btn-primary browse-image" type="button">Browse</button>
                        </div>
                    </div>
                </td>
                <td style="text-align: right">
                    <button class="btn btn-warning remove-images" type="button">Xoá hình ảnh </button>
                </td>
            </tr>
            </tbody>
            <tbody id="list_file_{{str_replace(['[', ']'], '_', $name)}}">
            @if($value)
                @foreach($value as $key => $item)
                    <tr>
                        <td>
                            <div class="preview file-preview" style="overflow: auto">
                                <div class="card file-preview-frame krajee-default  kv-preview-thumb">
                                    <img src="{{Storage::disk('admin')->url($item['url'])}}" class="img" style="max-width: 100px">
                                </div>
                            </div>
                            <div class="input-group file-caption-main">
                                <input type="hidden" class="form-control remove-image"
                                       name="{{$name}}[{{$key}}][_remove_]" value="0">
                                <input type="text" readonly class="form-control" name="{{$name}}[{{$key}}][name]"
                                       placeholder="Select image" value="{{$item['url']}}">
                                <input type="file" class="form-control file-image " name="{{$name}}[{{$key}}][url]"
                                       style="display: none" placeholder="Select image">
                                <div class="input-group-btn input-group-append">
                                    <button class="btn btn-primary browse-image" type="button">Browse</button>
                                </div>
                            </div>
                        </td>
                        <td style="text-align: right">
                            <button class="btn btn-warning remove-images" type="button">Xoá hình ảnh </button>
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
            <tbody>
            <tr>
                <td colspan="3">
                    <button
                        type="button"
                        class="btn btn-danger btn-sm add-more-image"
                        data-action-id="{{str_replace(['[', ']'], '_', $name)}}">Thêm hình ảnh
                    </button>
                </td>
            </tr>
            </tbody>
        @include('admin::form.help-block')
    </div>
</div>
