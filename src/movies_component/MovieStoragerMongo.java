package movies_component;

import com.google.gson.Gson;
import com.mongodb.*;
import com.mongodb.client.MongoCollection;
import com.mongodb.client.MongoDatabase;
import com.omertron.omdbapi.OMDBException;
import com.omertron.omdbapi.OmdbApi;
import com.omertron.omdbapi.model.OmdbVideoFull;
import com.omertron.omdbapi.tools.OmdbBuilder;
import org.bson.Document;
import org.json.JSONObject;

import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Class to collect metadata about the movies from OMDb, store them in MongoDB and handle the MongoDB
 * Created by Sofia on 4/7/2016.
 */
public class MovieStoragerMongo {
    // Fields for movies DB
    private static final String _MOVIE_JSON_  = "movie";
    private static final String _MOVIE_ID_ = "movie_id";

    private MongoDatabase _db;
    private String _coll_name_movies;

    /**
     * Connects to the given server:port to the database movies_component
     * @param host The host name of the server
     * @param port The port of the server
     */
    public MovieStoragerMongo(String host, int port) {
        MongoClient mongoClient = new MongoClient(host, port);
        _db = mongoClient.getDatabase("movies_component");
        _coll_name_movies = "all_movies";
    }

    /**
     * Inserts movie JSON to movies collection
     * @param id The movie's MySQL DB id
     * @param imdbID The movie's IMDb ID
     */
    private void addMovie(String id, String imdbID) throws OMDBException {
        MongoCollection<Document> coll = _db.getCollection(_coll_name_movies);
        String json = findMovie(imdbID);
        
        if(!json.isEmpty()) {
            Document doc = new Document(_MOVIE_JSON_, Document.parse(json))
                    .append(_MOVIE_ID_, id);

            coll.insertOne(doc);
        }
    }

    /**
     * Finds a movie based on its IMDb ID
     * @param imdbID The movie's ID
     * @return The movie's JSON string
     */
    private String findMovie(String imdbID) throws OMDBException {
        OmdbApi omdb = new OmdbApi();
        Gson gson = new Gson();
        OmdbVideoFull result = omdb.getInfo(new OmdbBuilder().setImdbId(imdbID).setPlotLong().build());
        return gson.toJson(result);
    }

    public JSONObject getMovie(String id) {
        MongoCollection<Document> coll = _db.getCollection(_coll_name_movies);

        Document docToFind = new Document("movie_id",id);
        return new JSONObject(coll.find(docToFind).first().toJson());
    }

    public void deleteMovie(String id) {
        MongoCollection<Document> coll = _db.getCollection(_coll_name_movies);

        Document docToDelete = new Document("movie_id", id);
        coll.findOneAndDelete(docToDelete);
    }

    /**
     * Main function to obtain metadata from OMDb and store them in a Mongo Database
     * @param args Main function's arguments; can be null
     * @throws SQLException In case the connection with the MySQL database fails
     */
    public static void main(String[] args) throws SQLException {
        MovieStoragerMongo mongoConnector = new MovieStoragerMongo("localhost", 27017);
        MovieStoragerSQL storagerSQL = new MovieStoragerSQL();

        // The mysql select statement
        String query = " SELECT id,imdb_url FROM `all_movies` ";

        ResultSet rs = storagerSQL.selectQuery(query);
        while (rs.next()) { // For each record/row
            String id = rs.getString("id");
            String imdbid = rs.getString("imdb_url").replace("http://www.imdb.com/title/","")
                    .replace("tttt","tt");
            System.out.println("ID: " + id + " IMDb ID: " + imdbid);
            if(!imdbid.isEmpty()) {
                try {
                    mongoConnector.addMovie(id, imdbid); // Store movie's metadata to MongoDB
                } catch (OMDBException e) { // In case the movie cannot be found in OMDb
                    storagerSQL.deleteMovie(id);
                }
            } else {
                storagerSQL.deleteMovie(id);
            }
        }
        if(!storagerSQL.closeConnection()) {
            System.out.println("Failed to close connection!");
        }
    }
}
