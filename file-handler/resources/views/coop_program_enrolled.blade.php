<!DOCTYPE html>
<html>
<head>
    <title>Program Enrollment</title>
</head>
<body>
    <h2>Hello {{ $cooperative->name }},</h2>
    <p>We’re pleased to inform you that your cooperative has been successfully enrolled in the following program:</p>

    <ul>
        <li><strong>Program:</strong> {{ $program->name }}</li>
        <li><strong>Start Date:</strong> {{ now()->format('F d, Y') }}</li>
        <li><strong>Status:</strong> Ongoing</li>
    </ul>

    <p>Thank you for your continued partnership with the Provincial Cooperative Development Office.</p>
    <p>— PCDO Team</p>
</body>
</html>
