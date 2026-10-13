@extends('layouts.admin')

@section('title', 'Dashboard')
@section('pageTitle', 'Dashboard')
@section('pageSubtitle', 'Selasa, 13 Oktober 2026 · Ringkasan performa dan operasional atelier hari ini')
{{-- ⚠️ Tanggal masih hardcode. Nanti dari controller (Carbon::now()). --}}

@section('content')

<x-empty-state
    title="Area Konten Dashboard"
    message="Statistik, grafik, dan tabel transaksi akan ditampilkan di sini. Bagian ini akan diisi oleh tim yang menangani modul admin." />

@endsection