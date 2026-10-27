<x-mail::message>
# {{ t('mail.backup_headline', 'New database backup attached') }}

{{ t('mail.backup_body',
    'A full copy of the shop database backup is attached to this message (:name). Store it somewhere safe — the server keeps its own copy in storage/app/backups, but this email gives you an independent one.',
    ['name' => $name ?? '']) }}

Thanks,<br>
{{ $shopName }}
</x-mail::message>
