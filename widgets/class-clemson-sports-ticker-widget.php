<?php
class Elementor_Clemson_Sports_Ticker_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'clemson_sports_ticker';
    }

    public function get_title() {
        return __('Clemson Sports Ticker', 'clemson-sports-ticker');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function render() {
        ?>
        <div id="clemson-sports-ticker"></div>
        <script type="text/babel">
            const { useState, useEffect } = React;

            function ClemsonSportsTicker() {
                const [sports, setSports] = useState([]);
                const [selectedSport, setSelectedSport] = useState('All');
                const [lastUpdated, setLastUpdated] = useState(0);

                const fetchSportsData = async () => {
                    try {
                        const response = await fetch('/wp-json/clemson-sports-ticker/v1/sports');
                        const data = await response.json();
                        if (data.last_updated !== lastUpdated) {
                            setSports(data.entries);
                            setLastUpdated(data.last_updated);
                        }
                    } catch (error) {
                        console.error('Error fetching sports data:', error);
                    }
                };

                useEffect(() => {
                    fetchSportsData();
                    const interval = setInterval(fetchSportsData, 30000); // Fetch every 30 seconds
                    return () => clearInterval(interval);
                }, []);

                const uniqueSports = ['All', ...new Set(sports.map(sport => sport.sport))];

                const filteredSports = selectedSport === 'All'
                    ? sports
                    : sports.filter(sport => sport.sport === selectedSport);

                const formatDate = (dateString) => {
                    if (!dateString) return '';
                    const options = { weekday: 'short', month: 'short', day: 'numeric' };
                    return new Date(dateString + 'T00:00:00').toLocaleDateString('en-US', options);
                };

                const formatTime = (timeString) => {
                    if (!timeString || timeString === 'TBA') return 'TBA';
                    const [hours, minutes] = timeString.split(':');
                    const date = new Date();
                    date.setHours(parseInt(hours, 10));
                    date.setMinutes(parseInt(minutes, 10));
                    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                };

                return (
                    <div className="cst-container">
                        <div className="cst-content">
                            <div className="cst-controls">
                                <select
                                    value={selectedSport}
                                    onChange={(e) => setSelectedSport(e.target.value)}
                                    className="cst-sport-select"
                                >
                                    {uniqueSports.map(sport => (
                                        <option key={sport} value={sport}>{sport}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="cst-events-container">
                                <div className="swiper-container">
                                    <div className="swiper-wrapper">
                                        {filteredSports.map(sport => (
                                            <div key={sport.id} className="swiper-slide">
                                                <div className="cst-event">
                                                    <div className="cst-event-date">
                                                        {formatDate(sport.date)} - {formatTime(sport.time) || 'TBA'}
                                                    </div>
                                                    <div className="cst-event-sport">
                                                        {sport.sport}
                                                    </div>
                                                    <div className="cst-event-teams">
                                                        {sport.team1} 
                                                        <span className="cst-event-score">
                                                            {sport.score1 || sport.score2 ? `${sport.score1 || 0} - ${sport.score2 || 0}` : 'vs'}
                                                        </span> 
                                                        {sport.team2}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                
                                {/* Navigation Arrows outside the slider */}
                                <div className="swiper-button-next"></div>
                                <div className="swiper-button-prev"></div>
                            </div>
                        </div>
                    </div>
                );
            }

            ReactDOM.render(<ClemsonSportsTicker />, document.getElementById('clemson-sports-ticker'));

            // Initialize Swiper
            new Swiper('.swiper-container', {
                slidesPerView: 'auto',
                spaceBetween: 0,
                freeMode: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        </script>
        <?php
    }
}