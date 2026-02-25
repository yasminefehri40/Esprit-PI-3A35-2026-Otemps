<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-event-weather',
    description: 'Vérifie la météo des événements à venir et annule ceux dont les conditions sont défavorables (< 10°C ou > 40°C)',
)]
class CheckEventWeatherCommand extends Command
{
    public function __construct(
        private WeatherService $weatherService,
        private EventRepository $eventRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Vérification météo des événements OTEMPS');

        $events = $this->eventRepository->findUpcomingActiveEvents();

        if (empty($events)) {
            $io->info('Aucun événement actif dans les 5 prochains jours à vérifier.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('%d événement(s) trouvé(s) dans les 5 prochains jours.', count($events)));
        $io->newLine();

        $cancelled = 0;

        foreach ($events as $event) {
            $io->text(sprintf(
                '→ <info>%s</info> | %s | %s',
                $event->getTitre(),
                $event->getLieu(),
                $event->getDateDebut()->format('d/m/Y à H:i')
            ));

            $weather = $this->weatherService->getWeather($event->getLieu(), $event->getDateDebut());

            if (!$weather) {
                $io->warning('  Impossible de récupérer la météo. Vérifiez WEATHER_API_KEY dans .env.local');
                $io->newLine();
                continue;
            }

            $io->text(sprintf(
                '  Météo prévue : <comment>%d°C</comment> (ressenti %d°C), %s — humidité %d%%, vent %d km/h',
                $weather['temperature'],
                $weather['feels_like'],
                $weather['description'],
                $weather['humidity'],
                $weather['wind_speed']
            ));

            if ($this->weatherService->isBadWeather($weather)) {
                $reason = $this->weatherService->getBadWeatherReason($weather);
                $event->setStatut('annulé_météo');
                $cancelled++;
                $io->caution(sprintf('  ✗ ANNULÉ — %s', $reason));
            } else {
                $io->text('  ✓ Conditions météo favorables.');
            }

            $io->newLine();
        }

        $this->em->flush();

        if ($cancelled > 0) {
            $io->warning(sprintf('%d événement(s) annulé(s) pour cause de météo défavorable.', $cancelled));
        } else {
            $io->success('Tous les événements ont des conditions météo favorables.');
        }

        return Command::SUCCESS;
    }
}
