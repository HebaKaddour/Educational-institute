<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans;
            direction: rtl;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
        h3 {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<h2>تقرير الطلاب</h2>
<p>إجمالي عدد الطلاب: {{ $total }}</p>

@foreach ($groups as $group)
    <h3>{{ $group['group'] }}</h3>
    <p>عدد الطلاب: {{ $group['total'] }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الرقم الوطني</th>
                <th>هاتف الطالب</th>
                <th>هاتف الولي</th>
                <th>الصف</th>
                <th>المواد المشتركة</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($group['students'] as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['full_name'] }}</td>
                    <td>{{ $student['identification_number'] }}</td>
                    <td>{{ $student['student_mobile'] }}</td>
                    <td>{{ $student['guardian_mobile'] }}</td>
                    <td>{{ $student['grade'] }}</td>
                    <td>
                        @foreach ($student['subjects'] as $subject)
                            {{ $subject['name'] }}@if (!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

</body>
</html>
