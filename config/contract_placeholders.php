<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contract template placeholders (variables)
    |--------------------------------------------------------------------------
    | Key = variable name used in template as {{ key }}
    | Value = label shown in UI + optional description
    */
    'placeholders' => [
        'client_name' => ['label' => 'Nom du client', 'desc' => 'Nom complet du client'],
        'client_phone' => ['label' => 'Téléphone client', 'desc' => 'Numéro du client'],
        'client_email' => ['label' => 'Email client', 'desc' => 'Adresse email'],
        'client_cin' => ['label' => 'CIN client', 'desc' => 'Carte d’identité nationale'],
        'client_address' => ['label' => 'Adresse client', 'desc' => 'Adresse postale'],
        'rental_start_date' => ['label' => 'Date de début', 'desc' => 'Début de la location'],
        'rental_end_date' => ['label' => 'Date de fin', 'desc' => 'Fin de la location'],
        'rental_days' => ['label' => 'Nombre de jours', 'desc' => 'Durée en jours'],
        'vehicle_name' => ['label' => 'Véhicule', 'desc' => 'Nom / modèle du véhicule'],
        'vehicle_plate' => ['label' => 'Immatriculation', 'desc' => 'Plaque du véhicule'],
        'vehicle_brand' => ['label' => 'Marque', 'desc' => 'Marque du véhicule'],
        'vehicle_model' => ['label' => 'Modèle', 'desc' => 'Modèle du véhicule'],
        'vehicle_year' => ['label' => 'Année', 'desc' => 'Année du véhicule'],
        'daily_rate' => ['label' => 'Prix journalier', 'desc' => 'Tarif par jour (MAD)'],
        'total_amount' => ['label' => 'Montant total', 'desc' => 'Total à payer (MAD)'],
        'deposit_amount' => ['label' => 'Caution', 'desc' => 'Montant caution (MAD)'],
        'company_name' => ['label' => 'Nom entreprise', 'desc' => 'Nom de l’entreprise (loueur)'],
        'company_ice' => ['label' => 'ICE entreprise', 'desc' => 'ICE du loueur'],
        'company_address' => ['label' => 'Adresse entreprise', 'desc' => 'Adresse du loueur'],
        'company_phone' => ['label' => 'Téléphone entreprise', 'desc' => 'Contact loueur'],
        'company_email' => ['label' => 'Email entreprise', 'desc' => 'Email du loueur'],
        'contract_number' => ['label' => 'Numéro de contrat', 'desc' => 'Référence unique'],
        'contract_date' => ['label' => 'Date du contrat', 'desc' => 'Date d’édition'],
        'signature_date' => ['label' => 'Date de signature', 'desc' => 'Date de signature'],
    ],

    'groups' => [
        'Client' => ['client_name', 'client_cin', 'client_phone', 'client_email', 'client_address'],
        'Véhicule' => ['vehicle_name', 'vehicle_plate', 'vehicle_brand', 'vehicle_model', 'vehicle_year'],
        'Réservation' => ['rental_start_date', 'rental_end_date', 'rental_days', 'contract_number', 'contract_date', 'signature_date'],
        'Tarification' => ['daily_rate', 'total_amount', 'deposit_amount'],
        'Entreprise' => ['company_name', 'company_ice', 'company_address', 'company_phone', 'company_email'],
    ],
];
