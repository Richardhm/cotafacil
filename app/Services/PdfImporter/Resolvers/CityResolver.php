<?php

namespace App\Services\PdfImporter\Resolvers;

use Illuminate\Support\Facades\DB;

class CityResolver
{
    /** @var array<string, int|null> Cache normalizedKey→id */
    private array $cache = [];

    public function resolve(string $cidade, string $uf): ?int
    {
        $key = $this->key($cidade, $uf);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $origens = DB::table('tabela_origens')->select('id', 'nome', 'uf')->get();

        foreach ($origens as $origem) {
            if ($this->key($origem->nome, $origem->uf) === $key) {
                $this->cache[$key] = $origem->id;
                return $origem->id;
            }
        }

        // Fallback: city name only (ignore UF)
        $normalizedCidade = $this->normalize($cidade);
        foreach ($origens as $origem) {
            if ($this->normalize($origem->nome) === $normalizedCidade) {
                $this->cache[$key] = $origem->id;
                return $origem->id;
            }
        }

        $this->cache[$key] = null;
        return null;
    }

    /**
     * Returns the city ID, creating a new row in tabela_origens if the city doesn't exist.
     * Returns ['id' => int, 'created' => bool].
     */
    public function resolveOrCreate(string $cidade, string $uf): array
    {
        $existing = $this->resolve($cidade, $uf);

        if ($existing !== null) {
            return ['id' => $existing, 'created' => false];
        }

        $newId = DB::table('tabela_origens')->insertGetId([
            'nome'       => $cidade,
            'uf'         => strtoupper($uf),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Overwrite null cache entry with the new ID
        $this->cache[$this->key($cidade, $uf)] = $newId;

        return ['id' => $newId, 'created' => true];
    }

    private function key(string $cidade, string $uf): string
    {
        return $this->normalize($cidade) . '_' . strtoupper($uf);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?? $value;
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
