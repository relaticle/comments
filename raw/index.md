# 

> 

<u-page-hero>
<template v-slot:title="">

Comments

</template>

<template v-slot:description="">

A full-featured commenting system for Filament panels with threaded replies, @mentions, emoji reactions, and real-time updates.

Drop-in integration with any Filament resource.

<callout color="amber" icon="i-lucide-triangle-alert">

**Alpha Software** — Breaking changes may occur between releases. Not recommended for production use.

</callout>
</template>

<template v-slot:links="">
<u-button color="neutral" size="xl" to="/getting-started/installation" trailing-icon="i-lucide-arrow-right">

Get started

</u-button>

<u-button color="neutral" size="xl" to="https://github.com/relaticle/comments" icon="simple-icons:github" variant="outline">

GitHub

</u-button>
</template>
</u-page-hero>

<div className="text-center,max-w-5xl,mx-auto">
<div className="aspect-video,rounded-lg,shadow-lg,overflow-hidden">

![Comments - threaded discussions in Filament](/preview.png)

</div>
</div>

<u-page-section>
<template v-slot:title="">

Why choose Comments?

</template>

<template v-slot:features="">
<u-page-feature icon="i-lucide-messages-square">
<template v-slot:title="">

Threaded Replies

</template>

<template v-slot:description="">

Nested comment threads with configurable depth limits. Users can reply to specific comments creating organized discussions.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-at-sign">
<template v-slot:title="">

@Mentions

</template>

<template v-slot:description="">

Autocomplete user mentions with a customizable resolver interface. Dispatches events for notification handling.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-smile">
<template v-slot:title="">

Emoji Reactions

</template>

<template v-slot:description="">

Six built-in emoji reactions with a configurable set. Users can react to comments with a single click.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-paperclip">
<template v-slot:title="">

File Attachments

</template>

<template v-slot:description="">

Upload images and documents to comments with configurable storage, size limits, and MIME type validation.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-radio">
<template v-slot:title="">

Real-time Updates

</template>

<template v-slot:description="">

Optional broadcasting via private channels with automatic polling fallback. Comments stay in sync across sessions.

</template>
</u-page-feature>

<u-page-feature icon="i-lucide-puzzle">
<template v-slot:title="">

Full Filament Integration

</template>

<template v-slot:description="">

Three integration patterns: slide-over action, table row action, and infolist entry. Works with any Filament resource.

</template>
</u-page-feature>
</template>
</u-page-section>

<u-page-section>
<template v-slot:title="">

Our Ecosystem

</template>

<template v-slot:description="">

Extend your Laravel applications with our ecosystem of complementary tools

</template>

<card-group>
<card icon="i-simple-icons-laravel" target="_blank" title="FilaForms" to="https://filaforms.app">

![FilaForms](https://filaforms.app/img/og-image.png)Visual form builder for all your public-facing forms.

</card>

<card icon="i-lucide-sliders" target="_blank" title="Custom Fields" to="https://relaticle.github.io/custom-fields">

![Custom Fields](https://relaticle.github.io/custom-fields/og-image.png)Let users add custom fields to any model without code changes.

</card>
</card-group>
</u-page-section>
