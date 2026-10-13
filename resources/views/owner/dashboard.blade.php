@extends('layouts.owner')

@section('title', 'Dashboard Utama')
@section('pageTitle', 'Dashboard Utama')
@section('pageSubtitle', 'Ringkasan performa finansial atelier, sirkulasi busana adat & kebaya couture, serta antrean tindakan.')
{{-- ⚠️ Tanggal masih hardcode di topbar owner. Nanti dari controller. --}}

@section('content')

<x-empty-state
    title="Area Konten Dashboard Owner"
    message="Ringkasan finansial, grafik tren transaksi, breakdown sewa vs beli, dan pusat kendali antrean akan ditampilkan di sini. Bagian ini akan diisi oleh tim yang menangani modul owner." />

@endsection