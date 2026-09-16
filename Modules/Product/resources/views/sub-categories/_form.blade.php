@php
    $categoryOptions = ['' => '-- নির্বাচন করুন --'];
    foreach ($categories as $cat) {
        $categoryOptions[$cat->id] = $cat->name;
    }
    $selectedParentId = old('parent_id', $subCategory->parent_id ?? $subCategory->category_id ?? '');
@endphp

<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::select
        name="parent_id"
        id="sub_category_parent_id"
        label="মূল ক্যাটাগরি"
        label-en="Parent Category"
        :options="$categoryOptions"
        :value="$selectedParentId"
        size="sm"
        :required="true"
    />

    <x-core::input
        name="name"
        id="sub_category_name"
        label="নাম"
        label-en="Name"
        placeholder="যেমন: মোবাইল ফোন"
        placeholder-en="e.g. Mobile Phone"
        :value="old('name', $subCategory->name ?? '')"
        size="sm"
        :required="true"
    />

    <x-core::textarea
        name="description"
        id="sub_category_description"
        label="বিবরণ"
        label-en="Description"
        placeholder="ঐচ্ছিক বিবরণ"
        placeholder-en="Optional description"
        :value="old('description', $subCategory->description ?? '')"
        rows="3"
        size="sm"
    />
</div>
