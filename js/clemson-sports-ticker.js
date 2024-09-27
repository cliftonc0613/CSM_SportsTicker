const { useState, useEffect, useRef } = React;

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
  const [events, setEvents] = useState([]);
  const [selectedSport, setSelectedSport] = useState('All Sports');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const sliderRef = useRef(null);

  useEffect(() => {
    console.log('SportsTicker component mounted');
    fetch('/wp-json/clemson-sports-ticker/v1/sports')
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        console.log('Fetched data:', data);
        // Filter out manual entries if show_manual_entries is not 'yes'
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

  const scroll = (direction) => {
    if (sliderRef.current) {
      const scrollAmount = direction === 'left' ? -200 : 200;
      sliderRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };

  const filteredEvents = selectedSport === 'All Sports' 
    ? events 
    : events.filter(event => event.sport === selectedSport);

  if (loading) {
    return <div className="cst-loading">Loading...</div>;
  }

  if (error) {
    return <div className="cst-error">{error}</div>;
  }
 

  return (
    <div className="cst-ticker" style={{ height: props.ticker_height && props.ticker_height.size && props.ticker_height.unit ? `${props.ticker_height.size}${props.ticker_height.unit}` : '100px' }}>
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
        <button onClick={() => scroll('left')} className="cst-button cst-button-left">
          &lt;
        </button>
        <button onClick={() => scroll('right')} className="cst-button cst-button-right">
          &gt;
        </button>
      </div>
      <div className="cst-slider-container">
        <div ref={sliderRef} className="cst-slider">
          {filteredEvents.map((event) => (
            <div key={event.id} className="cst-event">
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
          ))}
        </div>
      </div>
    </div>
  );
}

// Make the SportsTicker component available globally
window.SportsTicker = SportsTicker;
console.log('SportsTicker component loaded');