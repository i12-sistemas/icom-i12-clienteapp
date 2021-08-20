<table>
    <thead>
    <tr>
        <th>id</th>
        <th>Nome</th>
    </tr>
    </thead>
    <tbody>
    @foreach($dataset as $row)
        <tr>
            <td>{{ $row->id }}</td>
            <td>{{ $row->nome }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
