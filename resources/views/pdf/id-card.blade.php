<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Identitas Karyawan - {{ $employee->nik }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .id-card-container {
            width: 210mm;
            height: 148mm;
            margin: 0 auto;
            background: white;
            display: flex;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            page-break-after: always;
        }

        .id-card-front,
        .id-card-back {
            width: 50%;
            padding: 15px;
            border: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .id-card-front {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-right: none;
        }

        .id-card-back {
            background: white;
            border-left: none;
        }

        /* FRONT SIDE */
        .card-header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 8px;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .card-title {
            font-size: 10px;
            margin-top: 4px;
            opacity: 0.9;
        }

        .photo-container {
            text-align: center;
            margin: 10px 0;
        }

        .photo-placeholder {
            width: 60px;
            height: 75px;
            margin: 0 auto;
            background: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #999;
            text-align: center;
            padding: 5px;
        }

        .photo-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employee-info {
            font-size: 9px;
            line-height: 1.6;
            margin-top: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .label {
            font-weight: bold;
            flex: 0 0 45%;
        }

        .value {
            flex: 1;
            text-align: right;
        }

        .nik-section {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 4px;
            margin-top: 6px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .nik-label {
            font-size: 7px;
            opacity: 0.8;
        }

        .validity {
            font-size: 8px;
            text-align: center;
            margin-top: 6px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 4px;
            opacity: 0.9;
        }

        /* BACK SIDE */
        .back-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .back-header {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .detail-section {
            font-size: 8px;
            line-height: 1.8;
            margin-bottom: 8px;
        }

        .section-title {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 3px;
            font-size: 9px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 2px;
        }

        .detail-label {
            font-weight: bold;
            width: 35%;
            color: #333;
        }

        .detail-value {
            flex: 1;
            color: #666;
            word-break: break-word;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 8px;
            font-size: 8px;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }

        .signature-block {
            text-align: center;
            flex: 1;
        }

        .sig-line {
            border-top: 1px solid #333;
            width: 50px;
            margin: 0 auto 2px;
        }

        .sig-name {
            font-size: 7px;
        }

        .emergency-contact {
            background: #f9f9f9;
            padding: 4px 6px;
            border-radius: 3px;
            font-size: 8px;
            border-left: 3px solid #667eea;
        }

        .emergency-text {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 2px;
        }

        /* Responsive */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .id-card-container {
                box-shadow: none;
                width: 100%;
                height: auto;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="id-card-container">
        <!-- FRONT SIDE -->
        <div class="id-card-front">
            <div>
                <div class="card-header">
                    <div class="company-name">PERUSAHAAN XYZ</div>
                    <div class="card-title">KARTU IDENTITAS KARYAWAN</div>
                </div>

                <div class="photo-container">
                    <div class="photo-placeholder">
                        @if($employee->dokumen_pendukung && isset($employee->dokumen_pendukung['foto_selfie']))
                            <img src="{{ asset('storage/' . $employee->dokumen_pendukung['foto_selfie']) }}" alt="Foto">
                        @else
                            <span>FOTO</span>
                        @endif
                    </div>
                </div>

                <div class="employee-info">
                    <div class="info-row">
                        <span class="label">NAMA</span>
                        <span class="value">: {{ substr($employee->nama, 0, 20) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">NIK</span>
                        <span class="value">: {{ $employee->nik }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">DEPART.</span>
                        <span class="value">: {{ substr($employee->department, 0, 15) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">JABATAN</span>
                        <span class="value">: {{ substr($employee->jabatan, 0, 15) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">MASUK</span>
                        <span class="value">: {{ $employee->tanggal_masuk->format('d-m-Y') }}</span>
                    </div>

                    <div class="nik-section">
                        <div class="nik-label">NOMOR IDENTITAS</div>
                        <div>{{ $employee->no_ktp }}</div>
                    </div>
                </div>

                <div class="validity">
                    Berlaku selama hubungan kerja aktif
                </div>
            </div>
        </div>

        <!-- BACK SIDE -->
        <div class="id-card-back">
            <div class="back-content">
                <div>
                    <div class="back-header">INFORMASI DETAIL</div>

                    <div class="detail-section">
                        <div class="section-title">DATA PRIBADI</div>
                        <div class="detail-row">
                            <span class="detail-label">Tempat/Tgl Lahir</span>
                            <span class="detail-value">{{ $employee->tempat_lahir }}, {{ $employee->tanggal_lahir->format('d-m-Y') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jenis Kelamin</span>
                            <span class="detail-value">{{ $employee->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status Keluarga</span>
                            <span class="detail-value">{{ $employee->status_keluarga }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Umur</span>
                            <span class="detail-value">{{ $employee->age }} tahun</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="section-title">PEKERJAAN</div>
                        <div class="detail-row">
                            <span class="detail-label">Status Kerja</span>
                            <span class="detail-value">{{ $employee->status_pkwtt }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Pendidikan</span>
                            <span class="detail-value">{{ $employee->pendidikan ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Masa Kerja</span>
                            <span class="detail-value">{{ $employee->tenure_formatted }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="emergency-contact">
                        <div class="emergency-text">⚠️ KONTAK DARURAT</div>
                        Hubungi HRD jika kartu ini ditemukan
                    </div>

                    <div class="signature-section">
                        <div class="signature-block">
                            <div class="sig-line"></div>
                            <div class="sig-name">Karyawan</div>
                        </div>
                        <div class="signature-block">
                            <div class="sig-line"></div>
                            <div class="sig-name">HRD</div>
                        </div>
                        <div class="signature-block">
                            <div style="font-size: 7px;">{{ now()->format('d-m-Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
