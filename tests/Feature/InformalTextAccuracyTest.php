<?php

use App\Services\RuleBasedClassificationService;

beforeEach(function () {
    $this->classifier = new RuleBasedClassificationService();
});

test('informal text with Sundanese words for Jalan Rusak is accurately categorized', function () {
    $samples = [
        'jalan di majalaya meuni ancur logak parah pisan tlg benerin min',
        'aspal bolong jeglongan sewu di banjaran sering bikin motor tijalikeuh',
        'jalan amblas retak parah di ciwidey bahaya mun peuting teu katingali',
    ];

    foreach ($samples as $sample) {
        $result = $this->classifier->classify($sample);
        expect($result['kategori'])->toBe('Jalan Rusak');
    }
});

test('informal text with Sundanese words for Sampah dan Banjir is accurately categorized', function () {
    $samples = [
        'runtah numpuk bau pisan di solokanjeruk walungan citarum pinuh runtah',
        'got solokan mampet cileuncang banjir cileunyi mun hujan gede',
        'tumpukan sampah liar di baleendah bau bangke teu diangkut ku petugas',
    ];

    foreach ($samples as $sample) {
        $result = $this->classifier->classify($sample);
        expect($result['kategori'])->toBe('Lingkungan & Drainase');
    }
});

test('informal text with Sundanese words for Keamanan dan Ketertiban is accurately categorized', function () {
    $samples = [
        'aya geng motor mawa sajam di margahayu nongkrong bari mabok miras',
        'tawuran budak ngora di ciparay malem minggu sieun warga kaluar',
        'aksi begal motor dan premanisme di pameungpeuk meresahkan warga tlg patroli satpol pp',
    ];

    foreach ($samples as $sample) {
        $result = $this->classifier->classify($sample);
        expect($result['kategori'])->toBe('Keamanan & Ketertiban');
    }
});

test('informal text for Bantuan Sosial is accurately categorized', function () {
    $samples = [
        'bansos pkh tiasa cair iraha teu acan nampi di soreang',
        'bantuan blt beras sembako dtks can cair keneh ti kantor desa',
        'kartu kks bpnt teu meunang bantuan padahal warga miskin teu mampu',
    ];

    foreach ($samples as $sample) {
        $result = $this->classifier->classify($sample);
        expect($result['kategori'])->toBe('Bantuan Sosial');
    }
});
