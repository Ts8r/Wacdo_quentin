<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\JsonRequest;
use App\Http\JsonResponse;
use App\Repositories\DbCommandeRepository;
use Throwable;

final class CommandeController
{
    public function __construct(private readonly DbCommandeRepository $commandes)
    {
    }

    public function create(): void
    {
        try {
            $data = JsonRequest::body();

            $commande = $this->commandes->createForApi(
                idUser: $this->optionalIdUser($data),
                canal: $this->requiredCanal($data),
                produits: $this->normalizeLines($data['produits'] ?? [], 'produits'),
                menus: $this->normalizeLines($data['menus'] ?? [], 'menus'),
            );

            JsonResponse::send(['data' => $commande], 201);
        } catch (ValidationException $exception) {
            JsonResponse::send([
                'error' => 'validation_failed',
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            JsonResponse::send([
                'error' => 'server_error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function optionalIdUser(array $data): ?int
    {
        if (!array_key_exists('id_user', $data) || $data['id_user'] === null || $data['id_user'] === '') {
            return null;
        }

        $idUser = (int) $data['id_user'];

        if ($idUser <= 0) {
            throw ValidationException::forField('id_user', 'must be a positive integer');
        }

        return $idUser;
    }

    private function requiredCanal(array $data): string
    {
        $canal = trim((string) ($data['canal'] ?? ''));

        if ($canal === '') {
            throw ValidationException::forField('canal', 'field is required');
        }

        return $canal;
    }

    private function normalizeLines(mixed $lines, string $field): array
    {
        if (!is_array($lines)) {
            throw ValidationException::forField($field, 'must be an array');
        }

        $normalized = [];

        foreach ($lines as $index => $line) {
            if (!is_array($line)) {
                throw ValidationException::forField($field . '.' . $index, 'must be an object');
            }

            $id = (int) ($line['id'] ?? 0);
            $quantite = (int) ($line['quantite'] ?? 0);

            if ($id <= 0) {
                throw ValidationException::forField($field . '.' . $index . '.id', 'must be a positive integer');
            }

            if ($quantite <= 0) {
                throw ValidationException::forField($field . '.' . $index . '.quantite', 'must be a positive integer');
            }

            $taille = strtoupper(trim((string) ($line['taille'] ?? 'M')));

            if ($field === 'menus' && !in_array($taille, ['S', 'M', 'L'], true)) {
                throw ValidationException::forField($field . '.' . $index . '.taille', 'must be S, M or L');
            }

            if (isset($normalized[$id])) {
                if ($field === 'menus' && $normalized[$id]['taille'] !== $taille) {
                    throw ValidationException::forField($field . '.' . $index . '.taille', 'duplicate menu cannot use another size');
                }

                $normalized[$id]['quantite'] += $quantite;
                continue;
            }

            $normalized[$id] = [
                'id' => $id,
                'quantite' => $quantite,
                'taille' => $taille,
            ];
        }

        return array_values($normalized);
    }
}
