<?php
namespace App\DataResources;

interface IDataResource{

    /**
     * @return array
     */
    function toArray(bool $allowNull = false): array;

    /**
     * Register extra field dynamically
     * @param string $fieldName
     * @return void
     */
    public function withField(string $fieldName): void;

    /**
     * Register multi extra fields dynamically
     * @param array<string> $fields
     * @return void
     */
    public function withFields(array $fields): void;

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string;
}
