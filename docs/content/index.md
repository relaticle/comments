---
seo:
  title: Filament Comments System
  description: A full-featured commenting system for Filament panels with threaded replies, @mentions, emoji reactions, file attachments, and real-time updates.
  ogImage: /og-image.png
---

::u-page-hero
#title
Comments

#description
A full-featured commenting system for Filament panels with threaded replies, @mentions, emoji reactions, and real-time updates.

Drop-in integration with any Filament resource.

#links
  :::u-button
  ---
  color: neutral
  size: xl
  to: /getting-started/installation
  trailing-icon: i-lucide-arrow-right
  ---
  Get started
  :::

  :::u-button
  ---
  color: neutral
  icon: simple-icons:github
  size: xl
  to: https://github.com/relaticle/comments
  variant: outline
  ---
  GitHub
  :::
::

<div class="text-center max-w-5xl mx-auto">
  <div class="aspect-video rounded-lg shadow-lg overflow-hidden">
    <img src="/preview.png" alt="Comments - threaded discussions in Filament" class="w-full h-full object-cover object-top" />
  </div>
</div>

::u-page-section
#title
Why choose Comments?

#features
  :::u-page-feature
  ---
  icon: i-lucide-messages-square
  ---
  #title
  Threaded Replies

  #description
  Nested comment threads with configurable depth limits. Users can reply to specific comments creating organized discussions.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-at-sign
  ---
  #title
  @Mentions

  #description
  Autocomplete user mentions with a customizable resolver interface. Dispatches events for notification handling.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-smile
  ---
  #title
  Emoji Reactions

  #description
  Six built-in emoji reactions with a configurable set. Users can react to comments with a single click.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-paperclip
  ---
  #title
  File Attachments

  #description
  Upload images and documents to comments with configurable storage, size limits, and MIME type validation.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-radio
  ---
  #title
  Real-time Updates

  #description
  Optional broadcasting via private channels with automatic polling fallback. Comments stay in sync across sessions.
  :::

  :::u-page-feature
  ---
  icon: i-lucide-puzzle
  ---
  #title
  Full Filament Integration

  #description
  Three integration patterns: slide-over action, table row action, and infolist entry. Works with any Filament resource.
  :::
::

::u-page-section
#title
Our Ecosystem

#description
Extend your Laravel applications with our ecosystem of complementary tools

#default
  ::card-group
    :::card
    ---
    title: FilaForms
    icon: i-simple-icons-laravel
    to: https://filaforms.app
    target: _blank
    ---
    :img{src="https://filaforms.app/img/og-image.png" alt="FilaForms" class="mb-4 rounded-lg w-full pointer-events-none"}

    Visual form builder for all your public-facing forms.
    :::

    :::card
    ---
    title: Custom Fields
    icon: i-lucide-sliders
    to: https://relaticle.github.io/custom-fields
    target: _blank
    ---
    :img{src="https://relaticle.github.io/custom-fields/og-image.png" alt="Custom Fields" class="mb-4 rounded-lg w-full pointer-events-none"}

    Let users add custom fields to any model without code changes.
    :::
  ::
::
