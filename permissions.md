```
isGranted('READ', Material::class);
isGranted('READ', $material);

isGranted('BOOK', Material::class);
isGranted('BOOK', $material);

isGranted('CHANGE', $material);

isGranted('CREATE', Material::class);

isGranted('ARCHIVE', Material::class);
isGranted('ARCHIVE', $material);
```

```
isGranted('ADMIN');
```
