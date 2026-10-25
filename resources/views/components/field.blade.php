@props(['name', 'type' => 'text', 'value' => '', 'required' => false])
<label class="field">
    <span>{{ __('app.'.$name) }}</span>
    <input name="{{ $name }}" id="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' || $type === 'file' ? '' : old($name, $value) }}" @required($required) {{ $attributes }}>
    @error($name)<span class="field-error">{{ $message }}</span>@enderror
</label>

