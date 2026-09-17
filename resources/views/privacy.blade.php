@extends('layouts.frontend')
@section('title', 'Privacy & data use | '.config('app.name'))
@section('description', 'How SongChart handles account data, local browser history and aggregate product signals.')
@section('canonical_url', route('privacy'))

@push('head')
<meta name="robots" content="index,follow,max-image-preview:large">
@endpush

@section('content')
<div class="sc-container py-8 md:py-12" data-public-privacy>
    <div class="mx-auto max-w-3xl">
        <p class="text-sm font-semibold uppercase tracking-wide text-[var(--sc-text-secondary)]">Public trust</p>
        <h1 class="mt-2 text-3xl font-bold">Privacy & data use</h1>
        <p class="mt-4 text-[var(--sc-text-secondary)]">
            SongChart keeps product data collection bounded to the features that currently exist. This page describes the active data paths in the application.
        </p>

        <div class="mt-8 space-y-5">
            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-lg font-bold">Account data</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">
                        When you create and use an account, SongChart stores the account information required for authentication, security and account-owned features such as saved music entities. Saved items are private to the owning account unless a feature explicitly states otherwise.
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-lg font-bold">Recent activity on this device</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">
                        Recent searches and recently viewed entities are kept in this browser only. The current implementation caps each recent list, expires entries after 30 days and lets you clear that local state. It is not used to create an anonymous server-side identity.
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-lg font-bold">Aggregate product signals</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">
                        SongChart records bounded aggregate search counts to understand search demand and zero-result quality. The active product-signal contract does not store raw search queries, query hashes, user or session identifiers, IP addresses, user agents or request payloads.
                    </p>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-lg font-bold">External destinations</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--sc-text-secondary)]">
                        Canonical music pages may link to governed external listening or reference destinations. Following an external link takes you to another service whose own privacy practices apply there.
                    </p>
                </div>
            </x-ui.card>
        </div>

        <p class="mt-8 text-sm text-[var(--sc-text-secondary)]">
            This disclosure reflects the current executable SongChart product. Material new data collection should update this page together with its repository-owned data contract before release.
        </p>
    </div>
</div>
@endsection
