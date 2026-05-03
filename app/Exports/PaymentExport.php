<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    private $no = 0;

    public function __construct(private $data) {}

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DATA PEMBAYARAN'],
            ['Kremo - Kredit Motor Online'],
            ['Tanggal Export: ' . now()->format('d F Y H:i') . ' WIB'],
            [],
            [
                'No',
                'Tanggal Bayar',
                'Nama Pelanggan',
                'Motor',
                'Angsuran Ke',
                'Nominal (Rp)',
                'Metode Pembayaran'
            ]
        ];
    }

    public function map($row): array
    {
        $this->no++;

        return [
            $this->no,
            $row->tgl_bayar ? $row->tgl_bayar->format('d/m/Y') : '-',
            $row->kredit->pengajuanKredit->pelanggan->nama_pelanggan ?? '-',
            $row->kredit->pengajuanKredit->motor->nama_motor ?? '-',
            $row->angsuran_ke,
            $row->total_bayar,
            $row->payment_type ? 'Midtrans' : 'Manual',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A5:G' . $highestRow)->applyFromArray($styleArray);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]
            ],
        ];
    }
}
