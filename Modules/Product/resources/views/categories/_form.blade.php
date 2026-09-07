<div style="display:flex; flex-direction:column; gap:14px;">
    <x-core::input
        name="name"
        id="category_name"
        label="নাম"
        label-en="Name"
        placeholder="যেমন: মুদি পণ্য"
        placeholder-en="e.g. Grocery Items"
        :value="old('name', $category->name ?? '')"
        size="sm"
        :required="true"
    />

    <x-core::textarea
        name="description"
        id="category_description"
        label="বিবরণ"
        label-en="Description"
        placeholder="ঐচ্ছিক বিবরণ"
        placeholder-en="Optional description"
        :value="old('description', $category->description ?? '')"
        rows="3"
        size="sm"
    />
</div>
