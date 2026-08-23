<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    /**
     * Database Master 31 Kecamatan & Desa/Kelurahan Resmi Kabupaten Bandung
     */
    protected static array $wilayahBandung = [
        'Soreang' => [
            'lat' => -7.0252, 'lng' => 107.5197,
            'desa' => ['Pamekaran', 'Sadu', 'Sekarwangi', 'Soreang', 'Cingcin', 'Karamatmulya', 'Sukajadi', 'Panyirapan']
        ],
        'Baleendah' => [
            'lat' => -6.9950, 'lng' => 107.6335,
            'desa' => ['Andir', 'Baleendah', 'Bojongmalaka', 'Jelekong', 'Malakasari', 'Manggahang', 'Rancamanyar', 'Wargamekar']
        ],
        'Dayeuhkolot' => [
            'lat' => -6.9839, 'lng' => 107.6253,
            'desa' => ['Cangkuang Barat', 'Cangkuang Kulon', 'Cangkuang Wetan', 'Citeureup', 'Dayeuhkolot', 'Pasawahan', 'Sukapura']
        ],
        'Bojongsoang' => [
            'lat' => -6.9742, 'lng' => 107.6401,
            'desa' => ['Bojongsari', 'Bojongsoang', 'Buahbatu', 'Cipagalo', 'Lengkong', 'Tegalluar']
        ],
        'Margahayu' => [
            'lat' => -6.9789, 'lng' => 107.5755,
            'desa' => ['Margahayu Selatan', 'Margahayu Tengah', 'Sayati', 'Sukamenak', 'Sulaeman']
        ],
        'Margaasih' => [
            'lat' => -6.9536, 'lng' => 107.5458,
            'desa' => ['Cigondewah Hilir', 'Lagadar', 'Margaasih', 'Mekar Rahayu', 'Nanjung', 'Rahayu']
        ],
        'Katapang' => [
            'lat' => -7.0055, 'lng' => 107.5685,
            'desa' => ['Banyusari', 'Cilampeni', 'Gandasari', 'Katapang', 'Pangauban', 'Sangkanhurip', 'Sukamukti']
        ],
        'Kutawaringin' => [
            'lat' => -7.0085, 'lng' => 107.5125,
            'desa' => ['Buninagara', 'Cibodas', 'Cilame', 'Gajahmekar', 'Jatisari', 'Jelegong', 'Kopo', 'Kutawaringin', 'Padasuka', 'Panyirapan', 'Sukamulya']
        ],
        'Cangkuang' => [
            'lat' => -7.0385, 'lng' => 107.5525,
            'desa' => ['Bandasari', 'Cangkuang', 'Ciluncat', 'Janggala', 'Nagrak', 'Pananjung', 'Tanjungsari']
        ],
        'Banjaran' => [
            'lat' => -7.0450, 'lng' => 107.5878,
            'desa' => ['Banjaran', 'Banjaran Wetan', 'Ciapus', 'Kamasan', 'Kiangroke', 'Margahurip', 'Mekarjaya', 'Neglasari', 'Pasirmulya', 'Sindangpanon', 'Tarajusari']
        ],
        'Pameungpeuk' => [
            'lat' => -7.0150, 'lng' => 107.5950,
            'desa' => ['Arjasari', 'Bojongkunci', 'Bojongmanggu', 'Langonsari', 'Rancamulya', 'Rancatungku', 'Sukasari']
        ],
        'Arjasari' => [
            'lat' => -7.0589, 'lng' => 107.6189,
            'desa' => ['Ancolmekar', 'Arjasari', 'Baros', 'Batukarut', 'Lebakwangi', 'Mangunjaya', 'Mekarjaya', 'Patrolsari', 'Pinggirsari', 'Rancakole', 'Wargaluyu']
        ],
        'Cimaung' => [
            'lat' => -7.0725, 'lng' => 107.5520,
            'desa' => ['Campakamulya', 'Cikalong', 'Cimaung', 'Cipinang', 'Jagabaya', 'Malasari', 'Mekarsari', 'Pasirhuni', 'Sukamaju', 'Warjabakti']
        ],
        'Ciparay' => [
            'lat' => -7.0392, 'lng' => 107.7125,
            'desa' => ['Babakan Peuteuy', 'Bumiwangi', 'Ciheulang', 'Cikawao', 'Cikoneng', 'Ciparay', 'Gunungleutik', 'Manggungharja', 'Mekar Laksana', 'Mekarsari', 'Pakutandang', 'Sarimahi', 'Serangmekar', 'Sigaracipta']
        ],
        'Majalaya' => [
            'lat' => -7.0514, 'lng' => 107.7567,
            'desa' => ['Biru', 'Bojong', 'Majakerta', 'Majalaya', 'Majasetra', 'Neglasari', 'Padamulya', 'Pasirwangi', 'Sukamaju', 'Sukanyuki', 'Wangisagara']
        ],
        'Solokanjeruk' => [
            'lat' => -7.0255, 'lng' => 107.7455,
            'desa' => ['Bojongemas', 'Cibodas', 'Langensari', 'Padamukti', 'Panyadap', 'Rancakasumba', 'Solokanjeruk']
        ],
        'Paseh' => [
            'lat' => -7.0755, 'lng' => 107.7885,
            'desa' => ['Cigentur', 'Cijagra', 'Cipaku', 'Cipedes', 'Drawati', 'Karangtunggal', 'Loa', 'Mekarpawitan', 'Sindangsari', 'Sukamanah', 'Sukamantri', 'Tangsimekar']
        ],
        'Ibun' => [
            'lat' => -7.1255, 'lng' => 107.7855,
            'desa' => ['Cibeet', 'Dukuh', 'Ibun', 'Karyalaksana', 'Laksana', 'Lampegan', 'Mekarsari', 'Neglasari', 'Pangguh', 'Sudi', 'Tanggulun']
        ],
        'Pacet' => [
            'lat' => -7.1055, 'lng' => 107.7255,
            'desa' => ['Cikawao', 'Cikitu', 'Cinanggela', 'Cipeujeuh', 'Girimulya', 'Mandalahaji', 'Maruyung', 'Mekarjaya', 'Nagrak', 'Pangauban', 'Sukarame', 'Tanjungwangi']
        ],
        'Kertasari' => [
            'lat' => -7.2155, 'lng' => 107.6855,
            'desa' => ['Cibeureum', 'Cihawuk', 'Cikembang', 'Neglawangi', 'Resmitinggal', 'Santosa', 'Sukapura', 'Tarumajaya']
        ],
        'Pangalengan' => [
            'lat' => -7.1722, 'lng' => 107.5656,
            'desa' => ['Banjarsari', 'Lamajang', 'Margaluyu', 'Margamekar', 'Margamukti', 'Margamulya', 'Pangalengan', 'Pulosari', 'Sukaluyu', 'Sukamanah', 'Tribaktimulya', 'Wanasuka', 'Warnasari']
        ],
        'Pasirjambu' => [
            'lat' => -7.0789, 'lng' => 107.4855,
            'desa' => ['Cibodas', 'Cikoneng', 'Cisondari', 'Cukanggenteng', 'Margamulya', 'Mekarmaju', 'Mekarsari', 'Pasirjambu', 'Sugihmukti', 'Tenjolaya']
        ],
        'Ciwidey' => [
            'lat' => -7.0945, 'lng' => 107.4589,
            'desa' => ['Ciwidey', 'Lebakmuncang', 'Nengkelan', 'Panundaan', 'Panyocokan', 'Rawabogo', 'Sukawening']
        ],
        'Rancabali' => [
            'lat' => -7.1450, 'lng' => 107.3950,
            'desa' => ['Alamendah', 'Cipelah', 'Indragiri', 'Patengan', 'Sukaresmi']
        ],
        'Cileunyi' => [
            'lat' => -6.9388, 'lng' => 107.7478,
            'desa' => ['Cibiru Hilir', 'Cibiru Wetan', 'Cileunyi Kulon', 'Cileunyi Wetan', 'Cimekar', 'Cinunuk']
        ],
        'Rancaekek' => [
            'lat' => -6.9678, 'lng' => 107.7656,
            'desa' => ['Bojongloa', 'Bojongsalam', 'Cangkuang', 'Haurpugur', 'Jelegong', 'Linggar', 'Nanjungmekar', 'Rancaekek Kencana', 'Rancaekek Kulon', 'Rancaekek Wetan', 'Sangiang', 'Sukamanah', 'Sukamulya', 'Tegalsumedang']
        ],
        'Cicalengka' => [
            'lat' => -6.9845, 'lng' => 107.8345,
            'desa' => ['Babakan Peuteuy', 'Cicalengka Kulon', 'Cicalengka Wetan', 'Cikuya', 'Dampit', 'Margaasih', 'Nagrog', 'Panenjoan', 'Tanjungwangi', 'Tenjolaya', 'Waluya']
        ],
        'Nagreg' => [
            'lat' => -7.0255, 'lng' => 107.8920,
            'desa' => ['Bojong', 'Ciaro', 'Ciherang', 'Citaman', 'Ganjar Sabar', 'Mandalawangi', 'Nagreg', 'Nagreg Kendan']
        ],
        'Cikancung' => [
            'lat' => -7.0210, 'lng' => 107.8150,
            'desa' => ['Cihanyir', 'Cikancung', 'Cikasungka', 'Ciluluk', 'Hegarmanah', 'Mandalasari', 'Mekarlaksana', 'Srirahayu', 'Tanjunglaya']
        ],
        'Cilengkrang' => [
            'lat' => -6.8922, 'lng' => 107.7125,
            'desa' => ['Cilengkrang', 'Cipanjalu', 'Ciporeat', 'Girimekar', 'Jatiendah', 'Melatiwangi']
        ],
        'Cimenyan' => [
            'lat' => -6.8689, 'lng' => 107.6655,
            'desa' => ['Ciburial', 'Cikadut', 'Cimenyan', 'Mandalamekar', 'Mekarmanik', 'Mekarsaluyu', 'Padasuka', 'Sindanglaya']
        ],
    ];

    /**
     * Dapatkan daftar 31 Kecamatan Kabupaten Bandung
     * GET /api/wilayah/kecamatan
     */
    public function getKecamatan(): JsonResponse
    {
        $list = [];
        foreach (self::$wilayahBandung as $nama => $info) {
            $list[] = [
                'nama' => $nama,
                'lat' => $info['lat'],
                'lng' => $info['lng'],
                'total_desa' => count($info['desa']),
            ];
        }

        // Urutkan abjad
        usort($list, fn($a, $b) => strcmp($a['nama'], $b['nama']));

        return response()->json([
            'status' => 'success',
            'data' => $list,
        ]);
    }

    /**
     * Dapatkan daftar Desa/Kelurahan per Kecamatan
     * GET /api/wilayah/desa?kecamatan=Soreang
     */
    public function getDesa(Request $request): JsonResponse
    {
        $kecamatan = $request->query('kecamatan');
        if (!$kecamatan || !isset(self::$wilayahBandung[$kecamatan])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kecamatan tidak ditemukan',
                'data' => [],
            ], 404);
        }

        $info = self::$wilayahBandung[$kecamatan];
        $desaList = $info['desa'];
        sort($desaList);

        return response()->json([
            'status' => 'success',
            'kecamatan' => $kecamatan,
            'lat' => $info['lat'],
            'lng' => $info['lng'],
            'data' => $desaList,
        ]);
    }
}
