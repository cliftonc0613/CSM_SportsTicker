<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Elementor_Clemson_Sports_Ticker_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'clemson_sports_ticker';
    }

    public function get_title() {
        return __( 'Clemson Sports Ticker', 'clemson-sports-ticker' );
    }

    public function get_icon() {
        return 'eicon-posts-ticker';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'clemson-sports-ticker' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'ticker_height',
            [
                'label' => __( 'Ticker Height', 'clemson-sports-ticker' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 500,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 120,
                ],
                'selectors' => [
                    '{{WRAPPER}} #clemson-sports-ticker' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'show_manual_entries',
            [
                'label' => __( 'Show Manual Entries', 'clemson-sports-ticker' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'clemson-sports-ticker' ),
                'label_off' => __( 'No', 'clemson-sports-ticker' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __( 'Style', 'clemson-sports-ticker' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __( 'Background Color', 'clemson-sports-ticker' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} #clemson-sports-ticker' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __( 'Text Color', 'clemson-sports-ticker' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} #clemson-sports-ticker' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $widget_id = $this->get_id();
        ?>
        <div id="clemson-sports-ticker-<?php echo esc_attr($widget_id); ?>"></div>
        <script type="text/babel" data-presets="react">
            const sports = [
                "All Sports",
                "Football",
                "Men's Basketball",
                "Women's Basketball",
                "Baseball",
                "Softball",
                "Men's Soccer",
                "Women's Soccer"
            ];

            function SportsTicker(props) {
                const [events, setEvents] = React.useState([]);
                const [selectedSport, setSelectedSport] = React.useState('All Sports');
                const [loading, setLoading] = React.useState(true);
                const [error, setError] = React.useState(null);
                const swiperRef = React.useRef(null);

                React.useEffect(() => {
                    console.log('SportsTicker component mounted');
                    console.log('Props:', props);
                    fetch('/wp-json/clemson-sports-ticker/v1/sports')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Fetched data:', data);
                            const filteredData = props.show_manual_entries === 'yes' 
                                ? data 
                                : data.filter(event => !event.id.startsWith('manual_'));
                            console.log('Filtered data:', filteredData);
                            setEvents(filteredData);
                            setLoading(false);
                        })
                        .catch(error => {
                            console.error('Error fetching sports data:', error);
                            setError('Failed to fetch sports data');
                            setLoading(false);
                        });
                }, [props.show_manual_entries]);

                React.useEffect(() => {
                    if (!loading && events.length > 0) {
                        swiperRef.current = new Swiper('.swiper-container', {
                            slidesPerView: 'auto',
                            spaceBetween: 10,
                            navigation: {
                                nextEl: '.swiper-button-next',
                                prevEl: '.swiper-button-prev',
                            },
                        });
                    }
                }, [loading, events]);

                const filteredEvents = selectedSport === 'All Sports' 
                    ? events 
                    : events.filter(event => event.sport === selectedSport);

                console.log('Rendering SportsTicker with events:', filteredEvents);

                if (loading) {
                    return <div className="cst-loading">Loading...</div>;
                }

                if (error) {
                    return <div className="cst-error">{error}</div>;
                }

                return (
                    <div className="cst-ticker" style={{ height: props.ticker_height ? `${props.ticker_height.size}${props.ticker_height.unit}` : '120px' }}>
                        <div className="cst-controls">
                            <select
                                value={selectedSport}
                                onChange={(e) => setSelectedSport(e.target.value)}
                                className="cst-select"
                            >
                                {sports.map((sport) => (
                                    <option key={sport} value={sport}>{sport}</option>
                                ))}
                            </select>
                        </div>
                        <div className="swiper-container">
                            <div className="swiper-wrapper">
                                {filteredEvents.map((event) => (
                                    <div key={event.id} className="swiper-slide">
                                        <div className="cst-event">
                                            <div className="cst-event-header">
                                                <span className="cst-event-sport">{event.sport}</span>
                                                <span className="cst-event-date">{event.date}</span>
                                            </div>
                                            <div className="cst-event-content">
                                                {event.score1 !== null && event.score2 !== null ? (
                                                    <>
                                                        <div className="cst-event-team">
                                                            <span>{event.team1}</span>
                                                            <span className="cst-event-score">{event.score1}</span>
                                                        </div>
                                                        <div className="cst-event-team">
                                                            <span>{event.team2}</span>
                                                            <span className="cst-event-score">{event.score2}</span>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <>
                                                        <div className="cst-event-teams">{`${event.team1} vs ${event.team2}`}</div>
                                                        <div className="cst-event-time">{event.time}</div>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="swiper-button-next"></div>
                            <div className="swiper-button-prev"></div>
                        </div>
                    </div>
                );
            }

            ReactDOM.render(
                <SportsTicker {...<?php echo json_encode($settings); ?>} />,
                document.getElementById('clemson-sports-ticker-<?php echo esc_js($widget_id); ?>')
            );
        </script>
        <?php
    }

    protected function content_template() {
        ?>
        <div id="clemson-sports-ticker-{{id}}"></div>
        <?php
    }
}