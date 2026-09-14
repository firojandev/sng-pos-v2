<x-core::input
    name="name"
    label="সম্পদের নাম"
    label-en="Asset Name"
    placeholder="যেমন: ফ্রিজ, দোকানের ফার্নিচার"
    size="sm"
    :value="$asset->name"
    :required="true"
/>

<x-core::input
    name="amount"
    type="number"
    step="0.01"
    min="0"
    label="পরিমাণ (৳)"
    label-en="Amount (৳)"
    placeholder="0.00"
    prefix="৳"
    size="sm"
    :value="$asset->amount"
    :required="true"
    :stepper="false"
/>

<x-core::textarea
    name="note"
    label="নোট"
    label-en="Note"
    placeholder="ঐচ্ছিক নোট লিখুন..."
    rows="3"
    size="sm"
    :value="$asset->note"
/>
